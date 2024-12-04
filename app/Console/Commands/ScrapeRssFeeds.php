<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScraperService;

class ScrapeRssFeeds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'scrape:rss';

    /**
     * The console command description.
     */
    protected $description = 'Scrape RSS feeds from the file located in storage';

    /**
     * ScraperService instance.
     */
    protected $scraperService;

    /**
     * Create a new command instance.
     */
    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting RSS scraping...');
        $this->scraperService->scrapeRssFeeds();
        $this->info('RSS scraping completed.');
    }
}
