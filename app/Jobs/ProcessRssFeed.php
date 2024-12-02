<?php

namespace App\Jobs;

use App\Services\ScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRssFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $rssUrl;

    /**
     * Create a new job instance.
     */
    public function __construct($rssUrl)
    {
        $this->rssUrl = $rssUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(ScraperService $scraperService)
    {
        try {
            $scraperService->processRssFeed($this->rssUrl);
        } catch (\Exception $e) {
            Log::error("Job failed for RSS URL: {$this->rssUrl} - " . $e->getMessage());
            throw $e; // Mark job as failed
        }
    }

    public $tries = 3; // Retry failed jobs up to 3 times
    public $backoff = 60; // Wait 60 seconds before retrying
}
