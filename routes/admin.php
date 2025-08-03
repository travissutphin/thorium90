<?php

use App\Http\Controllers\Admin\RoleController;
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
    Route::middleware('permission:view users')->group(function () {
        Route::get('/users', function () {
            return Inertia::render('admin/users/index');
        })->name('users.index');
    });

    Route::middleware('permission:create users')->group(function () {
        Route::get('/users/create', function () {
            return Inertia::render('admin/users/create');
        })->name('users.create');
    });

    Route::middleware('permission:edit users')->group(function () {
        Route::get('/users/{user}/edit', function () {
            return Inertia::render('admin/users/edit');
        })->name('users.edit');
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
        Route::get('/settings', function () {
            return Inertia::render('admin/settings/index');
        })->name('settings.index');
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
