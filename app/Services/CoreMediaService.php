<?php

namespace App\Services;

use App\Contracts\MediaPickerInterface;
use App\Models\Media;
use Illuminate\Support\Facades\Log;

class CoreMediaService implements MediaPickerInterface
{
    /**
     * Get paginated list of media files with optional filters
     */
    public function getMediaList(array $filters = [], int $perPage = 20): array
    {
        try {
            $query = Media::with('uploader')
                ->virusClean() // Only show virus-free files
                ->orderBy('created_at', 'desc');

            // Apply filters
            $this->applyFilters($query, $filters);

            $media = $query->paginate($perPage);

            return [
                'data' => $media->through(function ($item) {
                    return $this->formatMediaItem($item);
                }),
                'meta' => [
                    'current_page' => $media->currentPage(),
                    'last_page' => $media->lastPage(),
                    'per_page' => $media->perPage(),
                    'total' => $media->total(),
                    'from' => $media->firstItem(),
                    'to' => $media->lastItem(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('CoreMediaService: Failed to get media list', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'data' => collect([]),
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                ]
            ];
        }
    }

    /**
     * Get a specific media item by ID
     */
    public function getMediaItem(int $id): ?array
    {
        try {
            $media = Media::with('uploader')->virusClean()->find($id);
            
            return $media ? $this->formatMediaItem($media) : null;
        } catch (\Exception $e) {
            Log::error('CoreMediaService: Failed to get media item', [
                'error' => $e->getMessage(),
                'media_id' => $id
            ]);

            return null;
        }
    }

    /**
     * Get media files filtered by type
     */
    public function getMediaByType(string $type, array $filters = [], int $perPage = 20): array
    {
        $filters['type'] = $type;
        return $this->getMediaList($filters, $perPage);
    }

    /**
     * Search media files by filename, alt text, or description
     */
    public function searchMedia(string $query, array $filters = [], int $perPage = 20): array
    {
        $filters['search'] = $query;
        return $this->getMediaList($filters, $perPage);
    }

    /**
     * Apply filters to the media query
     */
    private function applyFilters($query, array $filters): void
    {
        // Filter by type
        if (!empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        // Filter by uploader
        if (!empty($filters['uploader'])) {
            $query->uploadedBy($filters['uploader']);
        }

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'LIKE', "%{$search}%")
                  ->orWhere('alt_text', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by public/private
        if (isset($filters['is_public'])) {
            $query->where('is_public', (bool) $filters['is_public']);
        }
    }

    /**
     * Format media item for consistent API response
     */
    private function formatMediaItem($media): array
    {
        return [
            'id' => $media->id,
            'filename' => $media->filename,
            'stored_filename' => $media->stored_filename,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'size' => $media->size,
            'human_size' => $media->human_size,
            'type' => $media->type,
            'alt_text' => $media->alt_text,
            'description' => $media->description,
            'tags' => $media->tags,
            'is_public' => $media->is_public,
            'url' => $media->url,
            'thumbnail_url' => $media->thumbnail_url,
            'is_image' => $media->is_image,
            'is_video' => $media->is_video,
            'is_audio' => $media->is_audio,
            'is_document' => $media->is_document,
            'uploader' => [
                'id' => $media->uploader?->id,
                'name' => $media->uploader?->name,
            ],
            'created_at' => $media->created_at,
            'updated_at' => $media->updated_at,
        ];
    }
}