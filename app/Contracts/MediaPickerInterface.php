<?php

namespace App\Contracts;

interface MediaPickerInterface
{
    /**
     * Get paginated list of media files with optional filters
     */
    public function getMediaList(array $filters = [], int $perPage = 20): array;

    /**
     * Get a specific media item by ID
     */
    public function getMediaItem(int $id): ?array;

    /**
     * Get media files filtered by type (image, video, etc.)
     */
    public function getMediaByType(string $type, array $filters = [], int $perPage = 20): array;

    /**
     * Search media files by filename, alt text, or description
     */
    public function searchMedia(string $query, array $filters = [], int $perPage = 20): array;
}