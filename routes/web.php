<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home page route
Route::get('/', [PublicPageController::class, 'index'])->name('home.show');

// Dashboard route (protected)
Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified', 'ensure.2fa'])->name('dashboard');

// SEO Routes
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

// Authentication routes
require __DIR__.'/auth.php';

// Specific page routes for better SEO and organization
Route::get('/about', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'about')->name('about');
Route::get('/contact', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'contact')->name('contact');
Route::get('/coming-soon', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'coming-soon')->name('coming-soon');
Route::get('/privacy-policy', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'privacy-policy')->name('privacy-policy');
Route::get('/terms-and-conditions', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'terms-and-conditions')->name('terms-and-conditions');
Route::get('/faq', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'faq')->name('faq');
Route::get('/our-team', [PublicPageController::class, 'showBySlug'])->defaults('slug', 'our-team')->name('our-team');

// Public page routes catch-all (placed at end to handle dynamic pages)
Route::get('/{page:slug}', [PublicPageController::class, 'show'])->name('pages.show');
