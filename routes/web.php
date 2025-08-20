<?php

use App\Http\Controllers\PageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [App\Http\Controllers\PublicPageController::class, 'index'])->name('home.show');

Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified', 'ensure.2fa'])->name('dashboard');

// SEO Routes
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

require __DIR__.'/auth.php';

// Public page routes (placed at end to act as catch-all)
Route::get('/{page:slug}', [App\Http\Controllers\PublicPageController::class, 'show'])->name('pages.show');
