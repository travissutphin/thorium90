<?php

namespace App\Features\Blog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\MediaPickerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlogMediaController extends Controller
{
    private MediaPickerInterface $mediaService;

    public function __construct(MediaPickerInterface $mediaService)
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:view media')->only(['getMediaForBlog']);
        
        $this->mediaService = $mediaService;
    }

    /**
     * Get media files for blog featured image selection
     */
    public function getMediaForBlog(Request $request)
    {
        try {
            $filters = $request->validate([
                'search' => 'nullable|string|max:255',
                'type' => 'nullable|string|in:image,video,audio,document',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            // For blog featured images, we primarily want images
            if (!isset($filters['type'])) {
                $filters['type'] = 'image';
            }

            $perPage = $filters['per_page'] ?? 12; // Smaller page size for picker modal
            unset($filters['per_page']);

            $result = $this->mediaService->getMediaList($filters, $perPage);
            
            // Extract the actual data array from Laravel's paginate structure
            $resultData = $result['data']->toArray();
            $actualMediaItems = $resultData['data'] ?? [];
            
            // Ensure boolean properties are properly set for frontend
            $processedItems = array_map(function ($item) {
                return array_merge($item, [
                    'is_image' => $item['type'] === 'image',
                    'is_video' => $item['type'] === 'video', 
                    'is_audio' => $item['type'] === 'audio',
                    'is_document' => $item['type'] === 'document',
                ]);
            }, $actualMediaItems);

            return response()->json([
                'success' => true,
                'data' => $processedItems, // Flattened array of media items
                'meta' => $result['meta'] // Keep original meta for pagination
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('BlogMediaController: Failed to get media for blog', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load media files'
            ], 500);
        }
    }

    /**
     * Get a specific media item for blog use
     */
    public function getMediaItem(Request $request, int $id)
    {
        try {
            $media = $this->mediaService->getMediaItem($id);

            if (!$media) {
                return response()->json([
                    'success' => false,
                    'message' => 'Media file not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $media
            ]);

        } catch (\Exception $e) {
            Log::error('BlogMediaController: Failed to get media item', [
                'error' => $e->getMessage(),
                'media_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load media file'
            ], 500);
        }
    }
}