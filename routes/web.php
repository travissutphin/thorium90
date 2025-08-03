<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'permission:view dashboard'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    
    Route::get('api-demo', function () {
        return Inertia::render('api-demo');
    })->name('api-demo');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
