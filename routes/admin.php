<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin routes - require admin role or higher
Route::middleware(['auth', 'verified', 'role.any:Super Admin,Admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin Dashboard
    Route::get('/', function () {
        return Inertia::render('admin/dashboard');
    })->name('dashboard');

    // User Management Routes
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    
    // Soft Delete Routes
    Route::middleware('permission:view users')->group(function () {
        Route::get('/users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
    });
    
    Route::middleware('permission:restore users')->group(function () {
        Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });
    
    Route::middleware('permission:force delete users')->group(function () {
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    });

    // User Role Management Routes
    Route::middleware('permission:manage user roles')->group(function () {
        Route::get('/users/{user}/roles', [UserRoleController::class, 'show'])->name('users.roles.show');
        Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::post('/users/roles/bulk', [UserRoleController::class, 'bulkAssign'])->name('users.roles.bulk');
    });

    // Role Management Routes (Super Admin only)
    Route::middleware('role:Super Admin')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // System Settings (Admin+ only)
    Route::middleware('permission:manage settings')->group(function () {
        Route::get('/settings', [App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
        Route::put('/settings/{key}', [App\Http\Controllers\Admin\AdminSettingsController::class, 'updateSingle'])->name('settings.update-single');
        Route::post('/settings/reset', [App\Http\Controllers\Admin\AdminSettingsController::class, 'reset'])->name('settings.reset');
        Route::get('/settings/category/{category}', [App\Http\Controllers\Admin\AdminSettingsController::class, 'getByCategory'])->name('settings.category');
        Route::get('/settings/export', [App\Http\Controllers\Admin\AdminSettingsController::class, 'export'])->name('settings.export');
        Route::post('/settings/import', [App\Http\Controllers\Admin\AdminSettingsController::class, 'import'])->name('settings.import');
    });

    // System Statistics (Admin+ only)
    Route::middleware('permission:view system stats')->group(function () {
        Route::get('/settings/stats', [App\Http\Controllers\Admin\AdminSettingsController::class, 'stats'])->name('settings.stats');
    });
});

// Content Management Routes - require content creator roles
Route::middleware(['auth', 'verified', 'role.any:Super Admin,Admin,Editor,Author'])->prefix('content')->name('content.')->group(function () {
    
    // Posts Management
    Route::middleware('permission:view posts')->group(function () {
        Route::get('/posts', function () {
            return Inertia::render('content/posts/index');
        })->name('posts.index');
    });

    Route::middleware('permission:create posts')->group(function () {
        Route::get('/posts/create', function () {
            return Inertia::render('content/posts/create');
        })->name('posts.create');
    });

    // Media Management
    Route::middleware('permission:upload media')->group(function () {
        Route::get('/media', function () {
            return Inertia::render('content/media/index');
        })->name('media.index');
    });
});
