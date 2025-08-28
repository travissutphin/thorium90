<?php

use Illuminate\Support\Facades\Route;
use App\Features\Blog\Controllers\Admin\AdminBlogPostController;
use App\Features\Blog\Controllers\Admin\AdminBlogCategoryController;
use App\Features\Blog\Controllers\Admin\AdminBlogTagController;

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
Route::prefix('blog')->name('blog.')->group(function () {
    
    // Blog Posts Management
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [AdminBlogPostController::class, 'index'])->name('index')
            ->middleware('permission:blog.posts.view');
        Route::get('/create', [AdminBlogPostController::class, 'create'])->name('create')
            ->middleware('permission:blog.posts.create');
        Route::post('/', [AdminBlogPostController::class, 'store'])->name('store')
            ->middleware('permission:blog.posts.create');
        Route::get('/{post}/edit', [AdminBlogPostController::class, 'edit'])->name('edit')
            ->middleware('permission:blog.posts.edit');
        Route::put('/{post}', [AdminBlogPostController::class, 'update'])->name('update')
            ->middleware('permission:blog.posts.edit');
        Route::delete('/{post}', [AdminBlogPostController::class, 'destroy'])->name('destroy')
            ->middleware('permission:blog.posts.delete');
    });
    
    // Blog Categories Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminBlogCategoryController::class, 'index'])->name('index')
            ->middleware('permission:blog.categories.view');
        Route::get('/create', [AdminBlogCategoryController::class, 'create'])->name('create')
            ->middleware('permission:blog.categories.create');
        Route::post('/', [AdminBlogCategoryController::class, 'store'])->name('store')
            ->middleware('permission:blog.categories.create');
        Route::get('/{category}/edit', [AdminBlogCategoryController::class, 'edit'])->name('edit')
            ->middleware('permission:blog.categories.edit');
        Route::put('/{category}', [AdminBlogCategoryController::class, 'update'])->name('update')
            ->middleware('permission:blog.categories.edit');
        Route::delete('/{category}', [AdminBlogCategoryController::class, 'destroy'])->name('destroy')
            ->middleware('permission:blog.categories.delete');
    });
    
    // Blog Tags Management
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/', [AdminBlogTagController::class, 'index'])->name('index')
            ->middleware('permission:blog.tags.view');
        Route::get('/create', [AdminBlogTagController::class, 'create'])->name('create')
            ->middleware('permission:blog.tags.create');
        Route::post('/', [AdminBlogTagController::class, 'store'])->name('store')
            ->middleware('permission:blog.tags.create');
        Route::get('/{tag}/edit', [AdminBlogTagController::class, 'edit'])->name('edit')
            ->middleware('permission:blog.tags.edit');
        Route::put('/{tag}', [AdminBlogTagController::class, 'update'])->name('update')
            ->middleware('permission:blog.tags.edit');
        Route::delete('/{tag}', [AdminBlogTagController::class, 'destroy'])->name('destroy')
            ->middleware('permission:blog.tags.delete');
    });
});