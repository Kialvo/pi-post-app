<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use LanguageDetection\Language;

class ScraperService
{
    protected $progress = [];
    protected $keywords = ['Inter Milan', 'Internazionale', 'San Siro', 'Inzaghi'];
    protected $openAiApiKey;
    protected $languageDetector;

    // Configuration for OpenAI usage
    protected $openAiModel = 'gpt-3.5-turbo';
    protected $openAiTemperature = 0.7;
    protected $openAiMaxTokens;
    protected $enableOpenAiRelevanceCheck = true;
    protected $enableOpenAiTranslation = true;
    protected $enableOpenAiSummary = true;

    public function __construct()
    {
        $this->openAiApiKey = env('OPENAI_API_KEY');
        $this->languageDetector = new Language;
        $this->openAiMaxTokens = (int) env('OPENAI_MAX_TOKENS', 1500);
    }

    /**
     * Scrape all configured RSS feeds
     */
    public function scrapeRssFeeds()
    {
        $rssUrls = config('rss_urls', []);

        if (empty($rssUrls)) {
            Log::error('No RSS URLs found in the configuration.');
            return [];
        }

        foreach ($rssUrls as $rssUrl) {
            try {
                Log::info("Fetching RSS feed", ['rssUrl' => $rssUrl]);
                $response = Http::timeout(15)->get($rssUrl);

                if ($response->ok()) {
                    $this->processRssFeed($rssUrl, $response->body());
                } else {
                    Log::error("Failed to fetch RSS feed", ['rssUrl' => $rssUrl, 'status' => $response->status()]);
                }
            } catch (\Exception $e) {
                Log::error("Error fetching RSS feed", ['rssUrl' => $rssUrl, 'error' => $e->getMessage()]);
            }
        }

        return $this->progress;
    }

    /**
     * Process RSS Feed content
     */
    private function processRssFeed($rssUrl, $rssContent)
    {
        $rssXml = @simplexml_load_string($rssContent, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_ERR_NONE);

        if (!$rssXml) {
            Log::error("Invalid XML in RSS feed", ['rssUrl' => $rssUrl]);
            return;
        }

        if (!isset($rssXml->channel->item)) {
            Log::warning("No items found in RSS feed", ['rssUrl' => $rssUrl]);
            return;
        }

        foreach ($rssXml->channel->item as $item) {
            $this->processRssItem($rssUrl, $item);

            // Add a short delay after each item to spread out requests
            usleep(500000); // 0.5 seconds
        }
    }

    /**
     * Process an individual RSS Item
     */
    private function processRssItem($rssUrl, $item)
    {
        try {
            $title = $this->cleanText((string)$item->title);
            $content = $this->cleanText((string)$item->description);
            $link = (string)$item->link;
            $date = (string)$item->pubDate;
            $formattedDate = Carbon::parse($date)->format('Y-m-d H:i:s');

            Log::info("Processing RSS item", ['title' => $title, 'link' => $link]);

            // Skip if we already have this post
            if (Post::where('url', $link)->exists()) {
                Log::info("Skipped (duplicate)", ['title' => $title, 'link' => $link]);
                return;
            }

            // Check if relevant
            if (!$this->isRelevantContent($title, $content)) {
                Log::info("Skipped (irrelevant content)", ['title' => $title, 'link' => $link]);
                return;
            }

            $fullContent = $this->scrapeFullContent($link);
            if (!$fullContent) {
                Log::warning("Failed to scrape content", ['title' => $title, 'link' => $link]);
                return;
            }

            // Detect language
            $originalLanguage = $this->detectLanguage($title . ' ' . $fullContent);

            // Translate if needed
            $translatedTitle = null;
            $translatedContent = null;
            if ($originalLanguage !== 'it' && $this->enableOpenAiTranslation) {
                $translatedTitle = $this->translateText($title, 'Italiano');
                $translatedContent = $this->translateText($fullContent, 'Italiano');
            }

            // Summarize if enabled
            $summary = null;
            if ($this->enableOpenAiSummary) {
                $summary = $this->generateParaphrasedSummary(
                    $fullContent,
                    $translatedContent,
                    $originalLanguage,
                    "Riformula il seguente testo in Italiano mantenendo la maggior parte dei dettagli, senza ridurlo significativamente, ma rendendolo più coerente e scorrevole:"
                );
            }

            Log::info("Summary generated", ['summary' => $summary]);

            Post::create([
                'url' => $link,
                'source' => parse_url($rssUrl, PHP_URL_HOST),
                'title' => $title,
                'translated_title' => $translatedTitle,
                'original_post_content' => $fullContent,
                'translated_post_content' => $translatedContent,
                'summary' => $summary,
                'date' => $formattedDate,
            ]);

            Log::info("Processed RSS item successfully", ['title' => $title, 'link' => $link]);
        } catch (\Exception $e) {
            Log::error("Error processing RSS item", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Scrape full content from a given URL
     */
    private function scrapeFullContent($url)
    {
        try {
            Log::info("Starting to scrape content from URL", ['url' => $url]);

            // Quick check for video pages
            if (stripos($url, 'video') !== false || stripos($url, 'watch') !== false) {
                Log::info("Skipping URL as it appears to be a video page", ['url' => $url]);
                return null;
            }

            $response = Http::timeout(15)->get($url);

            if (!$response->ok()) {
                Log::error("Failed to fetch page", ['url' => $url, 'status' => $response->status()]);
                return null;
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            // Try extracting from JSON-LD
            $structuredData = $crawler->filter('script[type="application/ld+json"]')->each(function (Crawler $node) {
                return json_decode($node->text(), true);
            });

            foreach ($structuredData as $data) {
                if (isset($data['@type']) && $data['@type'] === 'NewsArticle' && isset($data['articleBody'])) {
                    return $this->cleanText($data['articleBody']);
                }
            }

            // Generic selectors
            $selectors = [
                'article',
                'div.article-content',
                'div.main-content',
                'div.content',
                'p'
            ];

            $content = null;
            foreach ($selectors as $selector) {
                $nodes = $crawler->filter($selector);
                if ($nodes->count() > 0) {
                    $content = $nodes->each(function (Crawler $node) {
                        $text = $node->text();
                        if (
                            stripos($text, 'comment') !== false ||
                            stripos($text, 'reaction') !== false ||
                            stripos($text, 'related articles') !== false
                        ) {
                            return null;
                        }
                        return $this->cleanText($text);
                    });
                    $content = array_filter($content);
                    if (!empty($content)) {
                        break;
                    }
                }
            }

            if (empty($content)) {
                // Fallback: extract all <p> and filter
                $content = $this->extractMainContent($html);
            }

            if (empty($content)) {
                Log::warning("No content found", ['url' => $url]);
                return null;
            }

            return $this->cleanText(implode("\n", $content));
        } catch (\Exception $e) {
            Log::error("Error scraping content from URL", ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Determine if content is relevant based on keywords and optional OpenAI check
     */
    private function isRelevantContent($title, $content)
    {
        // Check keywords first
        foreach ($this->keywords as $keyword) {
            if (stripos($title, $keyword) !== false || stripos($content, $keyword) !== false) {
                return true;
            }
        }

        if (!$this->enableOpenAiRelevanceCheck || !$this->openAiApiKey) {
            return false;
        }

        $cacheKey = 'is_relevant_' . md5($title . $content);
        return Cache::remember($cacheKey, 86400, function () use ($title, $content) {
            $prompt = "Il seguente contenuto parla della squadra di calcio dell'Inter Milan? Rispondi solo con 'Yes' o 'No'.\n\nTitolo: $title\n\nContenuto: $content";
            $response = $this->callOpenAiApi($prompt, $this->openAiModel, 0.0, 10);
            return $response && strtolower(trim($response)) === 'yes';
        });
    }

    /**
     * Detect language of the given text
     */
    private function detectLanguage($text)
    {
        $cacheKey = 'language_' . md5($text);
        return Cache::remember($cacheKey, 86400, function () use ($text) {
            $results = $this->languageDetector->detect($text)->bestResults();
            if ($results instanceof \LanguageDetection\LanguageResult) {
                foreach ($results as $lang => $prob) {
                    return strtolower($lang);
                }
            }
            return 'unknown';
        });
    }

    /**
     * Translate text to target language using LibreTranslate first, then fallback to OpenAI if enabled
     */
    private function translateText($text, $targetLanguage)
    {
        if (!$this->enableOpenAiTranslation) {
            return $text;
        }

        $cacheKey = 'translate_' . md5($text . $targetLanguage);
        return Cache::remember($cacheKey, 86400, function () use ($text, $targetLanguage) {
            $libreTranslateUrl = env('LIBRETRANSLATE_URL', 'http://localhost:5000/translate');
            $response = Http::post($libreTranslateUrl, [
                'q' => $text,
                'source' => 'auto',
                'target' => $targetLanguage,
                'format' => 'text',
            ]);

            if ($response->ok() && isset($response->json()['translatedText'])) {
                return trim($response->json()['translatedText']);
            }

            if ($this->openAiApiKey) {
                Log::warning("LibreTranslate failed. Falling back to OpenAI for translation.");
                $prompt = "Traduci il seguente testo in $targetLanguage:\n\n$text";
                return $this->callOpenAiApi($prompt, $this->openAiModel, 0.3, $this->openAiMaxTokens);
            }

            Log::warning("Translation not available. Returning original.");
            return $text;
        });
    }

    /**
     * Generate a paraphrased summary (or paraphrased version) of the content
     */
    private function generateParaphrasedSummary($originalContent, $translatedContent = null, $originalLanguage = 'unknown', $promptIntro = "Paraphrase the following text:")
    {
        if (!$this->enableOpenAiSummary || !$this->openAiApiKey) {
            return null;
        }

        $cacheKey = 'summary_' . md5($originalContent . $translatedContent . $originalLanguage);
        return Cache::remember($cacheKey, 300, function () use ($originalContent, $translatedContent, $originalLanguage, $promptIntro) {
            $contentToSummarize = ($originalLanguage === 'it') ? $originalContent : ($translatedContent ?? $originalContent);
            $prompt = "$promptIntro\n\n$contentToSummarize";

            Log::info("Calling OpenAI for summary", ['prompt' => $prompt]);
            $response = $this->callOpenAiApiRaw($prompt, $this->openAiModel, $this->openAiTemperature, $this->openAiMaxTokens);

            if (!$response) {
                Log::warning("No response from OpenAI summary call.");
                return null;
            }

            Log::info("OpenAI full response for summary", ['response' => $response]);

            if (isset($response['choices'][0]['message']['content'])) {
                $summaryText = trim($response['choices'][0]['message']['content']);
                if (!$summaryText) {
                    Log::warning("Summary is empty after trimming.");
                }
                return $summaryText;
            }

            Log::warning("Failed to find content in OpenAI response.");
            return null;
        });
    }

    /**
     * Direct API call returning full JSON (for debugging)
     */
    private function callOpenAiApiRaw($prompt, $model, $temperature, $maxTokens)
    {
        if (!$this->openAiApiKey) {
            return null;
        }

        // Increased maxAttempts for rate-limiting and added a delay after the call
        $rateLimitKey = 'openai-api-calls';
        $maxAttempts = 300; // increased from 120
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            Log::warning("OpenAI API rate limit exceeded locally.");
            return null;
        }

        RateLimiter::hit($rateLimitKey, $decaySeconds);

        $response = retry(3, function () use ($model, $prompt, $temperature, $maxTokens) {
            $apiResponse = Http::withHeaders([
                'Authorization' => "Bearer {$this->openAiApiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Sei un assistente utile.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            // Add a short delay after each API call to spread out requests
            usleep(500000); // 0.5 second delay after each API call

            return $apiResponse;
        }, 2000);

        if ($response->ok()) {
            return $response->json();
        }

        Log::error("OpenAI API error", ['response' => $response->body()]);
        return null;
    }

    /**
     * Simple wrapper returning only the content
     */
    private function callOpenAiApi($prompt, $model, $temperature, $maxTokens)
    {
        $response = $this->callOpenAiApiRaw($prompt, $model, $temperature, $maxTokens);
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }
        return null;
    }

    /**
     * Clean text
     */
    private function cleanText($text)
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Extract main content fallback
     */
    private function extractMainContent($html)
    {
        $crawler = new Crawler($html);
        $paragraphs = $crawler->filter('p')->each(function (Crawler $node) {
            return $this->cleanText($node->text());
        });

        // Return paragraphs with more than 20 chars
        $content = array_filter($paragraphs, fn($para) => strlen($para) > 20);
        return $content ? array_values($content) : null;
    }
}
