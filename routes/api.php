<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API Routes with Laravel Sanctum Authentication
 * 
 * These routes are loaded by the RouteServiceProvider and all of them will
 * be assigned to the "api" middleware group. Make something great!
 * 
 * Authentication:
 * - All API routes use Laravel Sanctum for token-based authentication
 * - Role and permission middleware work seamlessly with API tokens
 * - CSRF protection is handled automatically for SPA authentication
 * 
 * Usage Examples:
 * 
 * Creating a token (from web routes or API):
 * ```php
 * $token = $user->createToken('api-token')->plainTextToken;
 * ```
 * 
 * Making authenticated API requests:
 * ```javascript
 * // With Bearer token
 * fetch('/api/user', {
 *     headers: {
 *         'Authorization': 'Bearer ' + token,
 *         'Accept': 'application/json',
 *     }
 * })
 * 
 * // With SPA authentication (same domain)
 * await fetch('/sanctum/csrf-cookie');
 * fetch('/api/user', {
 *     headers: {
 *         'Accept': 'application/json',
 *         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
 *     }
 * })
 * ```
 */

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes do not require authentication and are available to all users.
|
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '2.0.1'
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| These routes require authentication via Laravel Sanctum. Users must
| provide a valid API token or be authenticated via SPA session.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // User information endpoint
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
            'is_content_creator' => $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']),
        ]);
    });

    // Token management endpoints
    Route::prefix('tokens')->group(function () {
        
        // List user's tokens
        Route::get('/', function (Request $request) {
            return response()->json([
                'tokens' => $request->user()->tokens->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'name' => $token->name,
                        'abilities' => $token->abilities,
                        'last_used_at' => $token->last_used_at,
                        'expires_at' => $token->expires_at,
                        'created_at' => $token->created_at,
                    ];
                })
            ]);
        });

        // Create new token
        Route::post('/', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'abilities' => 'array',
                'abilities.*' => 'string',
            ]);

            $token = $request->user()->createToken(
                $request->name,
                $request->abilities ?? ['*']
            );

            return response()->json([
                'token' => $token->plainTextToken,
                'name' => $request->name,
                'abilities' => $request->abilities ?? ['*'],
            ], 201);
        });

        // Revoke token
        Route::delete('/{id}', function (Request $request, $id) {
            $request->user()->tokens()->where('id', $id)->delete();
            
            return response()->json(['message' => 'Token revoked successfully']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Role-Based API Routes
    |--------------------------------------------------------------------------
    |
    | These routes demonstrate how to use role and permission middleware
    | with API authentication. The same middleware works for both session
    | and token-based authentication.
    |
    */

    // Admin-only routes
    Route::middleware(['role:Super Admin,Admin'])->prefix('admin')->group(function () {
        
        Route::get('/users', function (Request $request) {
            return response()->json([
                'users' => \App\Models\User::with('roles', 'permissions')->get()
            ]);
        });

        Route::get('/roles', function (Request $request) {
            return response()->json([
                'roles' => \Spatie\Permission\Models\Role::with('permissions')->get()
            ]);
        });

        Route::get('/permissions', function (Request $request) {
            return response()->json([
                'permissions' => \Spatie\Permission\Models\Permission::all()
            ]);
        });
    });

    // Content management routes (Editor and above)
    Route::middleware(['role.any:Super Admin,Admin,Editor'])->prefix('content')->group(function () {
        
        Route::get('/pages', function (Request $request) {
            return response()->json([
                'message' => 'Content pages endpoint',
                'user_roles' => $request->user()->roles->pluck('name'),
                'access_level' => 'Editor+'
            ]);
        });
    });

    // Author routes (Author and above)
    Route::middleware(['role.any:Super Admin,Admin,Editor,Author'])->prefix('author')->group(function () {
        
        Route::get('/my-pages', function (Request $request) {
            return response()->json([
                'message' => 'Author pages endpoint',
                'user_roles' => $request->user()->roles->pluck('name'),
                'access_level' => 'Author+'
            ]);
        });
    });

    // Permission-based routes
    Route::middleware(['permission:manage user roles'])->group(function () {
        
        Route::get('/user-management', function (Request $request) {
            return response()->json([
                'message' => 'User management endpoint',
                'user_permissions' => $request->user()->getAllPermissions()->pluck('name'),
                'required_permission' => 'manage user roles'
            ]);
        });
    });
});
