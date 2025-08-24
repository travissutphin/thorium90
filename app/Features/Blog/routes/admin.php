<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Blog Admin Routes
|--------------------------------------------------------------------------
|
| Here are the admin blog routes that will be loaded by the
| BlogServiceProvider when the blog feature is enabled.
|
*/

// Blog admin routes - protected by auth and admin role middleware
Route::middleware(['blog.enabled'])->prefix('blog')->name('blog.')->group(function () {
    
    // Blog admin dashboard
    Route::get('/', function () {
        return response()->json([
            'message' => 'Blog admin dashboard - Phase 1 implementation',
            'user' => auth()->user()->name,
            'permissions' => auth()->user()->getPermissionsViaRoles()->pluck('name')
        ]);
    })->name('dashboard');
    
    // Blog posts admin (placeholder routes for Phase 1)
    Route::get('/posts', function () {
        return response()->json(['message' => 'Blog posts admin index']);
    })->name('posts.index')->middleware('permission:view blog admin');
    
    Route::get('/posts/create', function () {
        return response()->json(['message' => 'Create blog post form']);
    })->name('posts.create')->middleware('permission:create blog posts');
    
    // Blog categories admin
    Route::get('/categories', function () {
        return response()->json(['message' => 'Blog categories admin']);
    })->name('categories.index')->middleware('permission:view blog admin');
    
    // Blog tags admin
    Route::get('/tags', function () {
        return response()->json(['message' => 'Blog tags admin']);
    })->name('tags.index')->middleware('permission:view blog admin');
    
    // Blog comments admin (if enabled)
    Route::get('/comments', function () {
        if (!config('blog.features.comments')) {
            return response()->json(['message' => 'Comments feature is disabled'], 404);
        }
        return response()->json(['message' => 'Blog comments admin']);
    })->name('comments.index')->middleware('permission:view blog admin');
});

/*
|--------------------------------------------------------------------------
| Phase 2 Routes (Coming Soon)
|--------------------------------------------------------------------------
|
| These routes will be implemented in Phase 2 with proper controllers:
| - AdminBlogPostController for CRUD operations
| - AdminBlogCategoryController for category management
| - AdminBlogTagController for tag management
| - AdminBlogCommentController for comment moderation
|
*/