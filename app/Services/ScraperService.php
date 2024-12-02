<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ScraperService
{
    protected $progress = [];
    protected $keywords = ['Inter Milan', 'Internazionale', 'San Siro', 'Inzaghi'];

    public function scrapeRssFeeds()
    {
        $rssUrls = config('rss_urls', []);

        if (empty($rssUrls)) {
            Log::error('No RSS URLs found in the configuration.');
            return [];
        }

        foreach ($rssUrls as $rssUrl) {
            try {
                Log::info("Fetching RSS feed: $rssUrl");
                $response = Http::timeout(15)->get($rssUrl);

                if ($response->ok()) {
                    Log::info("Successfully fetched RSS feed: $rssUrl");
                    $rssContent = $response->body();
                    $this->processRssFeed($rssUrl, $rssContent);
                } else {
                    Log::error("Failed to fetch RSS feed: $rssUrl - Status: " . $response->status());
                }
            } catch (\Exception $e) {
                Log::error("Error fetching RSS feed: $rssUrl - " . $e->getMessage());
            }
        }

        return $this->progress;
    }

    private function processRssFeed($rssUrl, $rssContent)
    {
        try {
            $rssXml = simplexml_load_string($rssContent, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_ERR_NONE);

            if (!$rssXml) {
                Log::error("Error processing RSS feed: $rssUrl - Invalid XML");
                return;
            }

            foreach ($rssXml->channel->item as $item) {
                $this->processRssItem($rssUrl, $item);
            }
        } catch (\Exception $e) {
            Log::error("Error processing RSS feed: $rssUrl - " . $e->getMessage());
        }
    }

    private function processRssItem($rssUrl, $item)
    {
        try {
            $title = $this->cleanText((string)$item->title);
            $content = $this->cleanText((string)$item->description);
            $link = (string)$item->link;
            $date = (string)$item->pubDate;

            $formattedDate = Carbon::parse($date)->format('Y-m-d H:i:s');

            Log::info("Processing RSS item: $title - Link: $link");

            if (Post::where('url', $link)->exists()) {
                Log::info("Skipped (duplicate): $title");
                return;
            }

            if (!$this->isRelevantContent($title, $content)) {
                Log::info("Skipped: $title (irrelevant content)");
                return;
            }

            $fullContent = $this->scrapeFullContent($link);

            if (!$fullContent) {
                Log::warning("Failed to scrape content for RSS item: $title - Link: $link");
                return;
            }

            // Detect language of the title and content
            $originalLanguage = $this->detectLanguage($title . ' ' . $fullContent);
            // Translate to Italian if not already in Italian
            $translatedTitle = $originalLanguage !== 'it' ? $this->translateText($title, 'italian') : null;

            $translatedContent = $originalLanguage !== 'it' ? $this->translateText($fullContent, 'italian') : null;

            // Generate paraphrased summary
            $summary = $this->generateParaphrasedSummary($fullContent, $translatedContent, $originalLanguage);


            // Save the post in the database
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

            Log::info("Processed: $title");
        } catch (\Exception $e) {
            Log::error("Error processing RSS item: $rssUrl - " . $e->getMessage());
        }
    }

    private function scrapeFullContent($url)
    {
        try {
            Log::info("Starting to scrape content from URL: $url");

            // Check for video-related keywords in the URL
            if (stripos($url, 'video') !== false || stripos($url, 'watch') !== false) {
                Log::info("Skipping URL as it appears to be a video page: $url");
                return null;
            }

            $response = Http::timeout(15)->get($url);

            if (!$response->ok()) {
                Log::error("Failed to fetch page: $url - Status: " . $response->status());
                return null;
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            // Step 1: Check and extract JSON-LD structured data
            $structuredData = $crawler->filter('script[type="application/ld+json"]')->each(function (Crawler $node) {
                return json_decode($node->text(), true);
            });

            foreach ($structuredData as $data) {
                if (isset($data['@type']) && $data['@type'] === 'NewsArticle') {
                    if (isset($data['articleBody'])) {
                        Log::info("Extracted articleBody from structured data.");
                        return $this->cleanText($data['articleBody']);
                    }
                }
            }

            // Step 2: Scrape content using generic selectors
            $selectors = [
                'article',                  // Common tag for main content
                'div.article-content',      // Custom selectors for articles
                'div.main-content',
                'div.content',              // General content divs
                'p'                         // Paragraphs as fallback
            ];

            $content = null;

            foreach ($selectors as $selector) {
                $nodes = $crawler->filter($selector);
                if ($nodes->count() > 0) {
                    $content = $nodes->each(function (Crawler $node) {
                        // Skip sections containing unrelated elements
                        $nodeHtml = $node->html();
                        if (
                            stripos($nodeHtml, 'comment') !== false ||      // Skip comments
                            stripos($nodeHtml, 'reaction') !== false ||    // Skip reactions
                            stripos($nodeHtml, 'related articles') !== false
                        ) {
                            return null;
                        }
                        return $node->text();
                    });

                    // Remove null entries
                    $content = array_filter($content);
                    break;
                }
            }

            // Step 3: Additional logic for Mundo Deportivo
            if (strpos($url, 'mundodeportivo.com') !== false) {
                Log::info("Applying specific scraping logic for Mundo Deportivo.");

                // Target the main content container
                $specificContainer = $crawler->filter('div.article-content.col-12.col-md-8');
                if ($specificContainer->count() > 0) {
                    $content = $specificContainer->filter('p')->each(function (Crawler $node) {
                        $text = $node->text();

                        // Skip paragraphs that might be advertisements or unrelated content
                        if (
                            stripos($text, 'advertisement') !== false ||
                            stripos($text, 'recirculation') !== false
                        ) {
                            return null;
                        }
                        return $text;
                    });

                    // Remove null or empty entries
                    $content = array_filter($content);

                    if (!empty($content)) {
                        $cleanedContent = $this->cleanText(implode("\n", $content));
                        Log::info("Successfully scraped content for Mundo Deportivo URL: $url");
                        return $cleanedContent;
                    }
                }
            }

            // Step 4: Fallback to GPT-4 if no content is found
            if (empty($content)) {
                Log::info("No content found using selectors. Falling back to GPT-4.");
                $content = $this->useGPT4ForContentParsing($html, $url);
            }

            if (empty($content)) {
                Log::warning("No content found using selectors or GPT-4 for URL: $url");
                return null;
            }

            // Clean up and return the content
            $cleanedContent = $this->cleanText(implode("\n", $content));
            Log::info("Successfully scraped content for URL: $url");
            return $cleanedContent;

        } catch (\Exception $e) {
            Log::error("Error scraping content from URL: $url - " . $e->getMessage());
            return null;
        }
    }


    private function isRelevantContent($title, $content)
    {
        try {
            $apiKey = env('OPENAI_API_KEY');
            $prompt = "Determine if the following content is about the Inter Milan football team. Respond with 'Yes' or 'No'.\n\nTitle: $title\n\nContent: $content";

            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a content classifier.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.0,
                'max_tokens' => 10,
            ]);

            if ($response->ok() && strtolower(trim($response->json()['choices'][0]['message']['content'])) === 'yes') {
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error during content relevance check: " . $e->getMessage());
        }

        return false;
    }

    private function detectLanguage($text)
    {
        try {
            $apiKey = env('OPENAI_API_KEY');
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a language detection assistant. Respond only with the ISO 639-1 language code (e.g., "it" for Italian, "en" for English, "fr" for French). Do not provide any additional explanation.'],
                    ['role' => 'user', 'content' => "Detect the language of the following text:\n\n$text"],
                ],
                'temperature' => 0.0,
                'max_tokens' => 10,
            ]);

            if ($response->ok()) {
                return strtolower(trim($response->json()['choices'][0]['message']['content']));
            }
        } catch (\Exception $e) {
            Log::error("Error detecting language: " . $e->getMessage());
        }

        return 'unknown';
    }

    private function translateText($text, $targetLanguage)
    {
        try {
            $apiKey = env('OPENAI_API_KEY');
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional translator.'],
                    ['role' => 'user', 'content' => "Translate the following text to $targetLanguage:\n\n$text"],
                ],
                'temperature' => 0.7,
                'max_tokens' => 3000,
            ]);

            if ($response->ok()) {
                return trim($response->json()['choices'][0]['message']['content']);
            }
        } catch (\Exception $e) {
            Log::error("Error translating text: " . $e->getMessage());
        }

        return null;
    }

    private function generateParaphrasedSummary($originalContent, $translatedContent = null, $originalLanguage = 'unknown')
    {
        try {
            $apiKey = env('OPENAI_API_KEY');

            // Decide which content to summarize
            if ($originalLanguage === 'it') {
                $contentToSummarize = $originalContent; // Use the original content if it's in Italian
                Log::info("Generating summary for the original Italian content.");
            } else {
                $contentToSummarize = $translatedContent; // Use the translated content for non-Italian text
                Log::info("Generating summary for the translated content in Italian.");
            }

            // Call GPT-4 API to generate the summary
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional summarizer. Always provide the summary in Italian.'],
                    ['role' => 'user', 'content' => "Paraphrase the following text into a concise summary in Italian:\n\n$contentToSummarize"],
                ],
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            if ($response->ok()) {
                $summary = trim($response->json()['choices'][0]['message']['content']);
                Log::info("Successfully generated summary: $summary");
                return $summary;
            }
        } catch (\Exception $e) {
            Log::error("Error generating paraphrased summary: " . $e->getMessage());
        }

        return null;
    }



    private function cleanText($text)
    {
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim and return
        return trim($text);
    }

}
