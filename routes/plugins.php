<?php

use App\Http\Controllers\Admin\PluginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Plugin Routes
|--------------------------------------------------------------------------
|
| Here are the routes for plugin management in the admin area.
|
*/

Route::prefix('admin')->middleware(['web', 'auth', 'role:Admin'])->group(function () {
    Route::prefix('plugins')->name('admin.plugins.')->group(function () {
        Route::get('/', [PluginController::class, 'index'])->name('index');
        Route::get('/{plugin}', [PluginController::class, 'show'])->name('show');
        Route::post('/install', [PluginController::class, 'install'])->name('install');
        Route::post('/{plugin}/enable', [PluginController::class, 'enable'])->name('enable');
        Route::post('/{plugin}/disable', [PluginController::class, 'disable'])->name('disable');
        Route::delete('/{plugin}', [PluginController::class, 'uninstall'])->name('uninstall');
        Route::post('/bulk-action', [PluginController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/clear-cache', [PluginController::class, 'clearCache'])->name('clear-cache');
        Route::get('/api/stats', [PluginController::class, 'stats'])->name('stats');
        Route::get('/export', [PluginController::class, 'export'])->name('export');
    });
});
