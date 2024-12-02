<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('scraping-progress', function () {
    return true; // No user authentication required
});
