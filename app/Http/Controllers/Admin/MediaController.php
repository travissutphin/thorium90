<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Exception;

class MediaController extends Controller
{
    private MediaUploadService $mediaUploadService;

    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:view media')->only(['index', 'show']);
        $this->middleware('permission:upload media')->only(['create', 'store']);
        $this->middleware('permission:edit media')->only(['edit', 'update']);
        $this->middleware('permission:delete media')->only(['destroy']);
        
        $this->mediaUploadService = $mediaUploadService;
    }

    /**
     * Display a listing of media files
     */
    public function index(Request $request)
    {
        $query = Media::with('uploader')
            ->virusClean() // Only show virus-free files
            ->orderBy('created_at', 'desc');

        // Filter by type if specified
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by uploader if specified
        if ($request->filled('uploader')) {
            $query->uploadedBy($request->uploader);
        }

        // Search by filename
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'LIKE', "%{$search}%")
                  ->orWhere('alt_text', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $media = $query->paginate(20)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'filename' => $item->filename,
                    'stored_filename' => $item->stored_filename,
                    'mime_type' => $item->mime_type,
                    'extension' => $item->extension,
                    'size' => $item->size,
                    'human_size' => $item->human_size,
                    'type' => $item->type,
                    'metadata' => $item->metadata,
                    'alt_text' => $item->alt_text,
                    'description' => $item->description,
                    'tags' => $item->tags,
                    'is_public' => $item->is_public,
                    'url' => $item->url,
                    'thumbnail_url' => $item->thumbnail_url,
                    'is_image' => $item->isImage(),
                    'is_document' => $item->isDocument(),
                    'is_video' => $item->isVideo(),
                    'is_audio' => $item->isAudio(),
                    'uploader' => [
                        'id' => $item->uploader->id,
                        'name' => $item->uploader->name,
                        'email' => $item->uploader->email,
                    ],
                    'created_at' => $item->created_at->toISOString(),
                    'updated_at' => $item->updated_at->toISOString(),
                ];
            });

        // Get statistics for dashboard
        $stats = [
            'total_files' => Media::count(),
            'total_size' => Media::sum('size'),
            'images_count' => Media::ofType(Media::TYPE_IMAGE)->count(),
            'documents_count' => Media::ofType(Media::TYPE_DOCUMENT)->count(),
            'videos_count' => Media::ofType(Media::TYPE_VIDEO)->count(),
            'audio_count' => Media::ofType(Media::TYPE_AUDIO)->count(),
            'clean_files' => Media::virusClean()->count(),
            'pending_scan' => Media::where('scan_result', Media::SCAN_PENDING)->count(),
        ];

        return Inertia::render('admin/media/index', [
            'media' => $media,
            'stats' => $stats,
            'filters' => $request->only(['type', 'uploader', 'search']),
        ]);
    }

    /**
     * Show the form for uploading new media
     */
    public function create()
    {
        return Inertia::render('admin/media/create');
    }

    /**
     * Upload and store new media files
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:10', // Max 10 files at once
            'files.*' => 'required|file|max:512000', // Max 500MB per file
            'alt_text.*' => 'nullable|string|max:255',
            'description.*' => 'nullable|string|max:1000',
            'tags.*' => 'nullable|array',
            'tags.*.*' => 'string|max:50',
            'is_public' => 'boolean',
        ]);

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                $options = [
                    'alt_text' => $request->input("alt_text.{$index}"),
                    'description' => $request->input("description.{$index}"),
                    'tags' => $request->input("tags.{$index}", []),
                    'is_public' => $request->boolean('is_public', true),
                ];

                $media = $this->mediaUploadService->uploadFile(
                    $file,
                    auth()->id(),
                    $options
                );

                $uploadedFiles[] = [
                    'id' => $media->id,
                    'filename' => $media->filename,
                    'url' => $media->url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'type' => $media->type,
                    'size' => $media->human_size,
                ];

            } catch (Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];

                Log::error('Media upload failed', [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id()
                ]);
            }
        }

        $response = [
            'uploaded' => $uploadedFiles,
            'errors' => $errors,
            'success_count' => count($uploadedFiles),
            'error_count' => count($errors),
        ];

        if (count($uploadedFiles) > 0) {
            $message = count($uploadedFiles) . ' file(s) uploaded successfully';
            if (count($errors) > 0) {
                $message .= ', ' . count($errors) . ' file(s) failed';
            }

            return redirect()->route('admin.media.index')
                ->with('success', $message)
                ->with('upload_results', $response);
        }

        return redirect()->back()
            ->withErrors(['upload' => 'All uploads failed'])
            ->with('upload_results', $response);
    }

    /**
     * Display the specified media file
     */
    public function show(Media $media)
    {
        $media->load('uploader');

        $mediaData = [
            'id' => $media->id,
            'filename' => $media->filename,
            'stored_filename' => $media->stored_filename,
            'path' => $media->path,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'size' => $media->size,
            'human_size' => $media->human_size,
            'type' => $media->type,
            'metadata' => $media->metadata,
            'alt_text' => $media->alt_text,
            'description' => $media->description,
            'tags' => $media->tags,
            'is_public' => $media->is_public,
            'url' => $media->url,
            'thumbnail_url' => $media->thumbnail_url,
            'is_image' => $media->isImage(),
            'is_document' => $media->isDocument(),
            'is_video' => $media->isVideo(),
            'is_audio' => $media->isAudio(),
            'is_clean' => $media->isVirusClean(),
            'is_infected' => $media->isInfected(),
            'scan_pending' => $media->isScanPending(),
            'uploader' => [
                'id' => $media->uploader->id,
                'name' => $media->uploader->name,
                'email' => $media->uploader->email,
                'avatar_url' => $media->uploader->getAvatarUrl(),
            ],
            'scanned_at' => $media->scanned_at?->toISOString(),
            'created_at' => $media->created_at->toISOString(),
            'updated_at' => $media->updated_at->toISOString(),
        ];

        return Inertia::render('admin/media/show', [
            'media' => $mediaData,
        ]);
    }

    /**
     * Show the form for editing media metadata
     */
    public function edit(Media $media)
    {
        $mediaData = [
            'id' => $media->id,
            'filename' => $media->filename,
            'alt_text' => $media->alt_text,
            'description' => $media->description,
            'tags' => $media->tags ?? [],
            'is_public' => $media->is_public,
            'url' => $media->url,
            'thumbnail_url' => $media->thumbnail_url,
            'type' => $media->type,
            'is_image' => $media->isImage(),
        ];

        return Inertia::render('admin/media/edit', [
            'media' => $mediaData,
        ]);
    }

    /**
     * Update media metadata
     */
    public function update(Request $request, Media $media)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_public' => 'boolean',
        ]);

        $media->update([
            'alt_text' => $request->alt_text,
            'description' => $request->description,
            'tags' => $request->tags,
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()->route('admin.media.show', $media)
            ->with('success', 'Media updated successfully');
    }

    /**
     * Delete media file and record
     */
    public function destroy(Media $media)
    {
        $filename = $media->filename;
        
        if ($this->mediaUploadService->deleteMedia($media)) {
            return redirect()->route('admin.media.index')
                ->with('success', "Media file '{$filename}' deleted successfully");
        }

        return redirect()->back()
            ->with('error', 'Failed to delete media file');
    }

    /**
     * Bulk operations on media files
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,make_public,make_private,add_tag,remove_tag',
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer|exists:media,id',
            'tag' => 'nullable|string|max:50|required_if:action,add_tag,remove_tag',
        ]);

        $media = Media::whereIn('id', $request->media_ids)->get();
        $count = 0;

        switch ($request->action) {
            case 'delete':
                foreach ($media as $item) {
                    if ($this->mediaUploadService->deleteMedia($item)) {
                        $count++;
                    }
                }
                return redirect()->back()
                    ->with('success', "Successfully deleted {$count} media files");

            case 'make_public':
                $count = Media::whereIn('id', $request->media_ids)
                    ->update(['is_public' => true]);
                return redirect()->back()
                    ->with('success', "Made {$count} media files public");

            case 'make_private':
                $count = Media::whereIn('id', $request->media_ids)
                    ->update(['is_public' => false]);
                return redirect()->back()
                    ->with('success', "Made {$count} media files private");

            case 'add_tag':
                foreach ($media as $item) {
                    $tags = $item->tags ?? [];
                    if (!in_array($request->tag, $tags)) {
                        $tags[] = $request->tag;
                        $item->update(['tags' => $tags]);
                        $count++;
                    }
                }
                return redirect()->back()
                    ->with('success', "Added tag '{$request->tag}' to {$count} media files");

            case 'remove_tag':
                foreach ($media as $item) {
                    $tags = $item->tags ?? [];
                    if (($key = array_search($request->tag, $tags)) !== false) {
                        unset($tags[$key]);
                        $item->update(['tags' => array_values($tags)]);
                        $count++;
                    }
                }
                return redirect()->back()
                    ->with('success', "Removed tag '{$request->tag}' from {$count} media files");
        }

        return redirect()->back()->with('error', 'Invalid action');
    }

    /**
     * API endpoint for file upload (for AJAX uploads)
     */
    public function uploadApi(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:512000',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_public' => 'boolean',
        ]);

        try {
            $options = [
                'alt_text' => $request->alt_text,
                'description' => $request->description,
                'tags' => $request->tags ?? [],
                'is_public' => $request->boolean('is_public', true),
            ];

            $media = $this->mediaUploadService->uploadFile(
                $request->file('file'),
                auth()->id(),
                $options
            );

            return response()->json([
                'success' => true,
                'media' => [
                    'id' => $media->id,
                    'filename' => $media->filename,
                    'url' => $media->url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'type' => $media->type,
                    'size' => $media->human_size,
                    'is_image' => $media->isImage(),
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
