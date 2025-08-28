<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Media Model
 * 
 * Handles file uploads and media management with security and optimization features.
 * Supports images, documents, videos, and audio files with automatic thumbnail generation.
 * 
 * Key Features:
 * - Secure file storage with virus scanning
 * - Automatic thumbnail generation for images
 * - Metadata extraction and storage
 * - Tag-based organization
 * - Permission-based access control
 * - Soft deletes for recovery
 * 
 * @property int $id
 * @property string $filename Original filename
 * @property string $stored_filename Stored filename with unique identifier
 * @property string $path Full path to file
 * @property string $disk Storage disk (default: public)
 * @property string $mime_type MIME type
 * @property string $extension File extension
 * @property int $size File size in bytes
 * @property string $type File type category (image, document, video, audio)
 * @property array|null $metadata Additional metadata (dimensions, duration, etc.)
 * @property string|null $thumbnail_path Path to thumbnail if applicable
 * @property string|null $alt_text Alt text for accessibility
 * @property string|null $description File description
 * @property array|null $tags Tags for organization
 * @property bool $is_public Public visibility
 * @property int $uploaded_by User ID who uploaded
 * @property \Carbon\Carbon|null $scanned_at Virus scan timestamp
 * @property string|null $scan_result Scan result status
 */
class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filename',
        'stored_filename',
        'path',
        'disk',
        'mime_type',
        'extension',
        'size',
        'type',
        'metadata',
        'thumbnail_path',
        'alt_text',
        'description',
        'tags',
        'is_public',
        'uploaded_by',
        'scanned_at',
        'scan_result',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'is_public' => 'boolean',
        'size' => 'integer',
        'scanned_at' => 'datetime',
    ];

    protected $dates = [
        'scanned_at',
        'deleted_at',
    ];

    // File type constants
    public const TYPE_IMAGE = 'image';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';

    // Scan result constants
    public const SCAN_CLEAN = 'clean';
    public const SCAN_INFECTED = 'infected';
    public const SCAN_PENDING = 'pending';
    public const SCAN_ERROR = 'error';

    /**
     * Get the user who uploaded this media
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the media file
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the full URL to the thumbnail (if exists)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->thumbnail_path);
    }

    /**
     * Get human readable file size
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the media file is an image
     */
    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    /**
     * Check if the media file is a document
     */
    public function isDocument(): bool
    {
        return $this->type === self::TYPE_DOCUMENT;
    }

    /**
     * Check if the media file is a video
     */
    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * Check if the media file is audio
     */
    public function isAudio(): bool
    {
        return $this->type === self::TYPE_AUDIO;
    }

    /**
     * Check if the file has been scanned and is clean
     */
    public function isVirusClean(): bool
    {
        return $this->scan_result === self::SCAN_CLEAN;
    }

    /**
     * Check if the file is infected
     */
    public function isInfected(): bool
    {
        return $this->scan_result === self::SCAN_INFECTED;
    }

    /**
     * Check if the file scan is pending
     */
    public function isScanPending(): bool
    {
        return $this->scan_result === self::SCAN_PENDING || $this->scan_result === null;
    }

    /**
     * Scope to filter by file type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter public files only
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to filter by uploader
     */
    public function scopeUploadedBy($query, int $userId)
    {
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Scope to filter virus-clean files only
     */
    public function scopeVirusClean($query)
    {
        return $query->where('scan_result', self::SCAN_CLEAN);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting, also delete the physical files
        static::deleting(function ($media) {
            if ($media->isForceDeleting()) {
                // Delete main file
                if (Storage::disk($media->disk)->exists($media->path)) {
                    Storage::disk($media->disk)->delete($media->path);
                }
                
                // Delete thumbnail if exists
                if ($media->thumbnail_path && Storage::disk($media->disk)->exists($media->thumbnail_path)) {
                    Storage::disk($media->disk)->delete($media->thumbnail_path);
                }
            }
        });
    }
}
