<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\{
    Auth\AuthenticatedSessionController,
    ScraperController,
    UserController,
    PostController
};

// Redirect root URL to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes (default provided by Laravel Breeze or similar)
require __DIR__ . '/auth.php';

// Routes for authenticated users
Route::middleware(['auth'])->group(function () {
    // Redirect after login to the appropriate dashboard
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;

        if ($role === 'administrator') {
            return redirect()->route('users.index');
        }

        if ($role === 'editor') {
            return redirect()->route('posts.index');
        }

        return redirect('/login')->withErrors(['Unauthorized access.']);
    })->name('dashboard');

    // Admin-only routes
    Route::middleware(CheckRole::class . ':administrator')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('users/{user}/confirm-delete', [UserController::class, 'confirmDelete'])->name('users.confirmDelete');
    });

    // Shared routes for administrators and editors
    Route::middleware(CheckRole::class . ':administrator|editor')->group(function () {
        Route::resource('posts', PostController::class);
        Route::get('/scraper', [ScraperController::class, 'showForm'])->name('scraper.form');
        Route::post('/scraper/process-url', [ScraperController::class, 'processUrl'])->name('scraper.url');
        Route::get('/run-scraper', [ScraperController::class, 'runScraper']);
    });
});
