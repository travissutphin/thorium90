<?php

use App\Http\Controllers\PageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public page routes
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

// SEO Routes
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');

require __DIR__.'/auth.php';
