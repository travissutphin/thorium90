<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Blog API Routes
|--------------------------------------------------------------------------
|
| Here are the blog API routes that will be loaded by the
| BlogServiceProvider if the blog API feature is enabled.
|
*/

// Blog API routes (optional - only loaded if blog.features.api is true)
Route::middleware(['blog.enabled'])->group(function () {
    
    // Public API endpoints
    Route::get('/posts', function () {
        return response()->json([
            'message' => 'Blog API posts endpoint - Phase 1 implementation',
            'data' => []
        ]);
    })->name('posts.index');
    
    Route::get('/posts/{post}', function ($post) {
        return response()->json([
            'message' => "Blog API post: {$post}",
            'data' => null
        ]);
    })->name('posts.show');
    
    Route::get('/categories', function () {
        return response()->json([
            'message' => 'Blog API categories endpoint',
            'data' => []
        ]);
    })->name('categories.index');
    
    Route::get('/tags', function () {
        return response()->json([
            'message' => 'Blog API tags endpoint',
            'data' => []
        ]);
    })->name('tags.index');
});

/*
|--------------------------------------------------------------------------
| Phase 2 API Routes (Coming Soon)
|--------------------------------------------------------------------------
|
| These routes will be implemented in Phase 2 with proper controllers:
| - ApiBlogPostController for blog post API
| - ApiBlogCategoryController for categories API
| - ApiBlogTagController for tags API
|
*/