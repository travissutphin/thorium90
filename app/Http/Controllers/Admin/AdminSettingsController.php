<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

/**
 * AdminSettingsController
 * 
 * This controller manages system-wide settings for the Multi-Role User Authentication
 * system. It provides comprehensive configuration management across all major
 * system categories with proper permission checks and validation.
 * 
 * Key Features:
 * - Category-based settings organization
 * - Type-safe value handling
 * - Comprehensive validation
 * - Audit logging for changes
 * - System statistics and monitoring
 * - Bulk settings operations
 * 
 * Protected Routes:
 * - All methods require 'manage settings' permission
 * - Security settings require 'manage security settings' permission
 * - System stats require 'view system stats' permission
 * - Audit logs require 'view audit logs' permission
 * 
 * Usage:
 * ```php
 * // View settings dashboard
 * GET /admin/settings
 * 
 * // Get settings by category
 * GET /admin/settings/category/{category}
 * 
 * // Update settings
 * PUT /admin/settings
 * 
 * // Reset settings to defaults
 * POST /admin/settings/reset
 * ```
 * 
 * @see App\Models\Setting
 */
class AdminSettingsController extends Controller
{
    /**
     * Constructor - Apply middleware for permission checks
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:manage settings')->except(['stats', 'auditLogs']);
        $this->middleware('permission:view system stats')->only(['stats']);
        $this->middleware('permission:view audit logs')->only(['auditLogs']);
    }

    /**
     * Log setting changes for audit purposes
     * TODO: Implement proper audit logging when activity log package is available
     */
    protected function logSettingChange(string $key, $newValue, $oldValue = null): void
    {
        // For now, just log to Laravel log
        \Log::info("Setting '{$key}' was updated", [
            'user_id' => auth()->id(),
            'setting_key' => $key,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Display the settings dashboard
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $settings = Setting::getGroupedByCategory();
        $stats = $this->getSystemStats();
        $categories = $this->getSettingsCategories();
        
        return Inertia::render('admin/settings/index', [
            'settings' => $settings,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    /**
     * Get settings for a specific category
     *
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByCategory(string $category)
    {
        $settings = Setting::getByCategory($category);
        $categoryInfo = $this->getCategoryInfo($category);
        
        return response()->json([
            'settings' => $settings,
            'category' => $categoryInfo,
        ]);
    }

    /**
     * Update system settings
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        // Validate all settings
        try {
            $this->validateSettings($settings);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $updatedSettings = [];
            
            foreach ($settings as $key => $data) {
                $value = $data['value'];
                $type = $data['type'] ?? 'string';
                $category = $data['category'] ?? 'general';
                $description = $data['description'] ?? null;
                $isPublic = $data['is_public'] ?? false;
                
                // Special handling for security settings
                if (str_starts_with($key, 'security.') && !auth()->user()->can('manage security settings')) {
                    continue; // Skip security settings if user doesn't have permission
                }
                
                $setting = Setting::set($key, $value, $type, $category, $description, $isPublic);
                $updatedSettings[] = $key;
                
                // Log the change for audit purposes
                $this->logSettingChange($key, $value, $setting->getOriginal('value') ?? null);
            }
            
            DB::commit();
            
            // Clear relevant caches
            $this->clearSettingsCache();
            
            return redirect()->back()->with('success', 'Settings updated successfully. Updated ' . count($updatedSettings) . ' settings.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a single setting
     *
     * @param \Illuminate\Http\Request $request
     * @param string $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSingle(Request $request, string $key)
    {
        // Check if this is a security setting
        if (str_starts_with($key, 'security.') && !auth()->user()->can('manage security settings')) {
            return response()->json(['error' => 'Insufficient permissions for security settings'], 403);
        }
        
        $type = $request->input('type', 'string');
        
        // Build validation rules based on type
        $rules = [
            'type' => 'required|in:string,integer,boolean,json,array',
        ];
        
        // Add type-specific validation for the value
        switch ($type) {
            case 'boolean':
                $rules['value'] = 'required|boolean';
                break;
            case 'integer':
                $rules['value'] = 'required|numeric';
                break;
            case 'json':
            case 'array':
                $rules['value'] = 'required|array';
                break;
            default:
                $rules['value'] = 'required|string';
        }
        
        // Add specific validation rules for certain settings
        if ($key === 'auth.default_role') {
            $roles = Role::pluck('name')->toArray();
            $rules['value'] = ['required', 'string', Rule::in($roles)];
        }
        
        if (str_contains($key, 'email') && str_contains($key, 'address')) {
            $rules['value'] = 'required|email';
        }
        
        if (str_contains($key, 'url')) {
            $rules['value'] = 'required|url';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $oldValue = Setting::get($key);
            
            $setting = Setting::set(
                $key,
                $request->input('value'),
                $request->input('type', 'string'),
                $request->input('category', 'general'),
                $request->input('description'),
                $request->input('is_public', false)
            );
            
            // Log the change
            $this->logSettingChange($key, $request->input('value'), $oldValue);
            
            return response()->json([
                'message' => 'Setting updated successfully',
                'setting' => [
                    'key' => $key,
                    'value' => $setting->getCastedValue(),
                    'type' => $setting->type,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update setting: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reset settings to default values
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $category = $request->input('category');
        
        if ($category && $category === 'security' && !auth()->user()->can('manage security settings')) {
            return redirect()->back()->with('error', 'Insufficient permissions to reset security settings.');
        }
        
        try {
            if ($category) {
                // Reset specific category
                Setting::where('category', $category)->delete();
                $message = "Settings for category '{$category}' have been reset to defaults.";
            } else {
                // Reset all settings (Super Admin only)
                if (!auth()->user()->hasRole('Super Admin')) {
                    return redirect()->back()->with('error', 'Only Super Admins can reset all settings.');
                }
                
                Setting::truncate();
                $message = 'All settings have been reset to defaults.';
            }
            
            // Re-run the settings seeder to restore defaults (outside transaction)
            \Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);
            
            // Clear cache
            $this->clearSettingsCache();
            
            // Log the reset action
            $this->logSettingChange('system.reset', $category ?? 'all');
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Get system statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $stats = $this->getSystemStats();
        
        return response()->json($stats);
    }

    /**
     * Export settings configuration
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $settings = Setting::all()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->getCastedValue(),
                'type' => $setting->type,
                'category' => $setting->category,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ];
        });
        
        $export = [
            'exported_at' => now()->toISOString(),
            'exported_by' => auth()->user()->email,
            'version' => Setting::get('app.version', '1.0.0'),
            'settings' => $settings,
        ];
        
        $filename = 'settings-export-' . now()->format('Y-m-d-H-i-s') . '.json';
        
        return response()->json($export)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Import settings configuration
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048',
        ]);
        
        try {
            $content = file_get_contents($request->file('file')->getRealPath());
            $data = json_decode($content, true);
            
            if (!$data || !isset($data['settings'])) {
                return redirect()->back()->with('error', 'Invalid settings file format.');
            }
            
            DB::beginTransaction();
            
            $imported = 0;
            foreach ($data['settings'] as $settingData) {
                // Skip security settings if user doesn't have permission
                if (str_starts_with($settingData['key'], 'security.') && !auth()->user()->can('manage security settings')) {
                    continue;
                }
                
                Setting::set(
                    $settingData['key'],
                    $settingData['value'],
                    $settingData['type'],
                    $settingData['category'],
                    $settingData['description'] ?? null,
                    $settingData['is_public'] ?? false
                );
                
                $imported++;
            }
            
            DB::commit();
            
            // Clear cache
            $this->clearSettingsCache();
            
            // Log the import
            $this->logSettingChange('system.import', $imported);
            
            return redirect()->back()->with('success', "Successfully imported {$imported} settings.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Failed to import settings: ' . $e->getMessage());
        }
    }

    /**
     * Get system statistics
     *
     * @return array
     */
    protected function getSystemStats(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'active' => User::whereNotNull('email_verified_at')->count(),
                'deleted' => User::onlyTrashed()->count(),
                'by_role' => User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('roles.name', DB::raw('count(*) as count'))
                    ->groupBy('roles.name')
                    ->pluck('count', 'name')
                    ->toArray(),
            ],
            'settings' => [
                'total' => Setting::count(),
                'by_category' => Setting::select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray(),
                'public' => Setting::where('is_public', true)->count(),
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'cache_enabled' => Setting::get('system.cache_enabled', true),
                'debug_mode' => Setting::get('system.debug_mode', false),
                'maintenance_mode' => Setting::get('app.maintenance_mode', false),
            ],
        ];
    }

    /**
     * Validate settings data
     *
     * @param array $settings
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateSettings(array $settings)
    {
        // Validate each setting individually
        foreach ($settings as $key => $data) {
            $type = $data['type'] ?? 'string';
            $value = $data['value'] ?? null;
            
            // Basic validation - ensure value exists
            if (!isset($data['value'])) {
                $validator = Validator::make([], []);
                $validator->errors()->add($key, "The {$key} value is required.");
                throw new \Illuminate\Validation\ValidationException($validator);
            }
            
            // Type-specific validation
            $rules = [];
            switch ($type) {
                case 'boolean':
                    $rules['value'] = 'required|boolean';
                    break;
                case 'integer':
                    $rules['value'] = 'required|numeric';
                    break;
                case 'json':
                case 'array':
                    $rules['value'] = 'required|array';
                    break;
                default:
                    $rules['value'] = 'required|string';
            }
            
            // Add specific validation rules for certain settings
            if ($key === 'auth.default_role') {
                $roles = Role::pluck('name')->toArray();
                $rules['value'] = ['required', 'string', Rule::in($roles)];
            }
            
            if (str_contains($key, 'email') && str_contains($key, 'address')) {
                $rules['value'] = 'required|email';
            }
            
            if (str_contains($key, 'url')) {
                $rules['value'] = 'required|url';
            }
            
            // Validate this individual setting
            $validator = Validator::make(['value' => $value], $rules);
            
            if ($validator->fails()) {
                // Re-throw with proper context
                $contextValidator = Validator::make([], []);
                foreach ($validator->errors()->get('value') as $error) {
                    $contextValidator->errors()->add($key, str_replace('value', $key, $error));
                }
                throw new \Illuminate\Validation\ValidationException($contextValidator);
            }
        }
    }

    /**
     * Get available settings categories
     *
     * @return array
     */
    protected function getSettingsCategories(): array
    {
        return [
            'application' => [
                'name' => 'Application',
                'description' => 'Basic application configuration',
                'icon' => 'settings',
            ],
            'authentication' => [
                'name' => 'Authentication',
                'description' => 'Login and registration settings',
                'icon' => 'shield-check',
            ],
            'user_management' => [
                'name' => 'User Management',
                'description' => 'User account and profile settings',
                'icon' => 'users',
            ],
            'security' => [
                'name' => 'Security',
                'description' => 'Password policies and security settings',
                'icon' => 'lock',
            ],
            'email' => [
                'name' => 'Email',
                'description' => 'Email configuration and templates',
                'icon' => 'mail',
            ],
            'features' => [
                'name' => 'Features',
                'description' => 'Feature toggles and experimental features',
                'icon' => 'toggle-left',
            ],
            'system' => [
                'name' => 'System',
                'description' => 'System-level configuration',
                'icon' => 'server',
            ],
        ];
    }

    /**
     * Get category information
     *
     * @param string $category
     * @return array
     */
    protected function getCategoryInfo(string $category): array
    {
        $categories = $this->getSettingsCategories();
        
        return $categories[$category] ?? [
            'name' => ucfirst($category),
            'description' => 'Settings for ' . $category,
            'icon' => 'settings',
        ];
    }


    /**
     * Clear settings-related caches
     */
    protected function clearSettingsCache()
    {
        Setting::clearCache();
        
        // Clear other relevant caches
        Cache::tags(['settings'])->flush();
    }
}
