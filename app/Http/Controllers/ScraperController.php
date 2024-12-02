<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScraperService;

class ScraperController extends Controller
{
    protected $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    public function showForm()
    {
        return view('scraper');
    }

    /**
     * Trigger the scraping process for RSS feeds.
     */
    public function runScraper()
    {
        // Start the scraping process
        $results = $this->scraperService->scrapeRssFeeds();

        // Return a response based on the results of the scraping
        if (!empty($results)) {
            return response()->json(['success' => 'Scraping completed successfully.', 'results' => $results]);
        }

        return response()->json(['error' => 'No RSS URLs found to scrape.'], 422);
    }
}
