<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ScrapingProgressUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        // Sanitize the message to ensure UTF-8 encoding
        $this->message = mb_convert_encoding($message, 'UTF-8', 'auto');
    }

    public function broadcastOn()
    {
        return new Channel('scraping-progress');
    }

    public function broadcastWith()
    {
        // Only send necessary fields
        return [
            'message' => mb_strcut($this->message, 0, 200), // Truncate message to 200 characters
        ];
    }
}
