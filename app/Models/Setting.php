<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Setting Model
 * 
 * This model manages system-wide configuration settings for the Multi-Role User
 * Authentication system. It provides a flexible key-value store with type casting
 * and caching capabilities.
 * 
 * Key Features:
 * - Type-safe value storage and retrieval
 * - Automatic caching for performance
 * - Category-based organization
 * - Public/private setting visibility
 * - JSON and array value support
 * 
 * Usage:
 * ```php
 * // Get a setting value
 * $value = Setting::get('app.name', 'Default App Name');
 * 
 * // Set a setting value
 * Setting::set('app.name', 'My Application');
 * 
 * // Get settings by category
 * $systemSettings = Setting::getByCategory('system');
 * 
 * // Check if setting exists
 * if (Setting::has('feature.enabled')) {
 *     // Setting exists
 * }
 * ```
 * 
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property string $category
 * @property string|null $description
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Cache key prefix for settings
     */
    const CACHE_PREFIX = 'setting:';

    /**
     * Cache duration in seconds (24 hours)
     */
    const CACHE_DURATION = 86400;

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are modified
        static::saved(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget('settings:category:' . $setting->category);
            Cache::forget('settings:all');
        });

        static::deleted(function ($setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            Cache::forget('settings:category:' . $setting->category);
            Cache::forget('settings:all');
        });
    }

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_DURATION,
            function () use ($key, $default) {
                $setting = self::where('key', $key)->first();
                
                if (!$setting) {
                    return $default;
                }

                return $setting->getCastedValue();
            }
        );
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $category
     * @param string|null $description
     * @param bool $isPublic
     * @return Setting
     */
    public static function set(
        string $key,
        $value,
        string $type = 'string',
        string $category = 'general',
        ?string $description = null,
        bool $isPublic = false
    ): Setting {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::castValueForStorage($value, $type),
                'type' => $type,
                'category' => $category,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        return $setting;
    }

    /**
     * Check if a setting exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key . ':exists',
            self::CACHE_DURATION,
            function () use ($key) {
                return self::where('key', $key)->exists();
            }
        );
    }

    /**
     * Get all settings by category
     *
     * @param string $category
     * @param bool $publicOnly
     * @return \Illuminate\Support\Collection
     */
    public static function getByCategory(string $category, bool $publicOnly = false)
    {
        $cacheKey = 'settings:category:' . $category . ($publicOnly ? ':public' : '');
        
        return Cache::remember(
            $cacheKey,
            self::CACHE_DURATION,
            function () use ($category, $publicOnly) {
                $query = self::where('category', $category);
                
                if ($publicOnly) {
                    $query->where('is_public', true);
                }
                
                return $query->get()->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->getCastedValue()];
                });
            }
        );
    }

    /**
     * Get all settings
     *
     * @param bool $publicOnly
     * @return \Illuminate\Support\Collection
     */
    public static function getAll(bool $publicOnly = false)
    {
        $cacheKey = 'settings:all' . ($publicOnly ? ':public' : '');
        
        return Cache::remember(
            $cacheKey,
            self::CACHE_DURATION,
            function () use ($publicOnly) {
                $query = self::query();
                
                if ($publicOnly) {
                    $query->where('is_public', true);
                }
                
                return $query->get()->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->getCastedValue()];
                });
            }
        );
    }

    /**
     * Delete a setting
     *
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            return $setting->delete();
        }
        
        return false;
    }

    /**
     * Get the casted value based on the type
     *
     * @return mixed
     */
    public function getCastedValue()
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Cast value for storage based on type
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected static function castValueForStorage($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json', 'array' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Get settings grouped by category
     *
     * @param bool $publicOnly
     * @return \Illuminate\Support\Collection
     */
    public static function getGroupedByCategory(bool $publicOnly = false)
    {
        $query = self::query();
        
        if ($publicOnly) {
            $query->where('is_public', true);
        }
        
        return $query->get()
            ->groupBy('category')
            ->map(function ($settings) {
                return $settings->mapWithKeys(function ($setting) {
                    return [$setting->key => [
                        'value' => $setting->getCastedValue(),
                        'type' => $setting->type,
                        'description' => $setting->description,
                        'is_public' => $setting->is_public,
                    ]];
                });
            });
    }
}
