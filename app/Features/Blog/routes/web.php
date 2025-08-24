<?php

use Illuminate\Support\Facades\Route;

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
    Route::get('/blog', function () {
        return response()->json(['message' => 'Blog index - Phase 1 implementation']);
    })->name('blog.index');
    
    Route::get('/blog/category/{category}', function ($category) {
        return response()->json(['message' => "Blog category: {$category}"]);
    })->name('blog.categories.show');
    
    Route::get('/blog/tag/{tag}', function ($tag) {
        return response()->json(['message' => "Blog tag: {$tag}"]);
    })->name('blog.tags.show');
    
    Route::get('/blog/{post}', function ($post) {
        return response()->json(['message' => "Blog post: {$post}"]);
    })->name('blog.posts.show');
});

/*
|--------------------------------------------------------------------------
| Phase 2 Routes (Coming Soon)
|--------------------------------------------------------------------------
|
| These routes will be implemented in Phase 2 with proper controllers:
| - BlogController for public blog pages
| - BlogPostController for individual post pages
| - BlogCategoryController for category listings
| - BlogTagController for tag listings
|
*/