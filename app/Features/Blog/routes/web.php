<?php

use Illuminate\Support\Facades\Route;
use App\Features\Blog\Controllers\BlogController;

/*
|--------------------------------------------------------------------------
| Blog Web Routes
|--------------------------------------------------------------------------
|
| Here are the public-facing blog routes that will be loaded by the
| BlogServiceProvider when the blog feature is enabled.
|
*/

// Blog feature gate - all routes are protected by blog.enabled middleware
Route::middleware(['blog.enabled'])->group(function () {
    
    // Blog index and listing routes
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    
    // Blog search
    Route::get('/blog/search', [BlogController::class, 'search'])->name('blog.search');
    
    // Blog archive
    Route::get('/blog/archive', [BlogController::class, 'archive'])->name('blog.archive');
    Route::get('/blog/archive/{year}', [BlogController::class, 'archive'])->name('blog.archive.year');
    Route::get('/blog/archive/{year}/{month}', [BlogController::class, 'archive'])->name('blog.archive.month');
    
    // Category and tag routes
    Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.categories.show');
    Route::get('/blog/tag/{tag}', [BlogController::class, 'tag'])->name('blog.tags.show');
    
    // Newsletter subscription
    Route::post('/blog/newsletter/subscribe', [BlogController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
    
    // Individual blog post (must be last to avoid conflicts)
    Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.posts.show');
});