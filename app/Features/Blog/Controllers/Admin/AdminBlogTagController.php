<?php

namespace App\Features\Blog\Controllers\Admin;

use App\Features\Blog\Models\BlogTag;
use App\Features\Blog\Services\BlogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogTagController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
        
        // Apply permission middleware
        $this->middleware('permission:blog.tags.view');
        $this->middleware('permission:blog.tags.create')->only(['create', 'store']);
        $this->middleware('permission:blog.tags.edit')->only(['edit', 'update']);
        $this->middleware('permission:blog.tags.delete')->only(['destroy']);
    }

    /**
     * Display a listing of blog tags.
     */
    public function index(): Response
    {
        $tags = BlogTag::withCount(['blogPosts'])
            ->orderBy('usage_count', 'desc')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/blog/tags/Index', [
            'tags' => $tags,
            'permissions' => [
                'canCreate' => auth()->user()->can('blog.tags.create'),
                'canEdit' => auth()->user()->can('blog.tags.edit'),
                'canDelete' => auth()->user()->can('blog.tags.delete'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create(): Response
    {
        return Inertia::render('admin/blog/tags/Create');
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:' . config('blog.validation.tag_name_max_length', 50),
            'slug' => 'nullable|string|max:' . config('blog.validation.slug_max_length', 255) . '|unique:blog_tags',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default color if not provided
        if (empty($validated['color'])) {
            $validated['color'] = '#00bcd4';
        }

        BlogTag::create($validated);

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Blog tag created successfully.');
    }

    /**
     * Display the specified tag.
     */
    public function show(BlogTag $tag): Response
    {
        $tag->load(['blogPosts' => function($query) {
            $query->with(['user', 'blogCategory'])->latest('created_at');
        }]);

        return Inertia::render('admin/blog/tags/Show', [
            'tag' => $tag
        ]);
    }

    /**
     * Show the form for editing the specified tag.
     */
    public function edit(BlogTag $tag): Response
    {
        return Inertia::render('admin/blog/tags/Edit', [
            'tag' => $tag
        ]);
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, BlogTag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:' . config('blog.validation.tag_name_max_length', 50),
            'slug' => 'nullable|string|max:' . config('blog.validation.slug_max_length', 255) . '|unique:blog_tags,slug,' . $tag->id,
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'description' => 'nullable|string',
        ]);

        $tag->update($validated);

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Blog tag updated successfully.');
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(BlogTag $tag): RedirectResponse
    {
        // Detach from all posts
        $tag->blogPosts()->detach();
        
        $tag->delete();

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Blog tag deleted successfully.');
    }

    /**
     * Bulk delete unused tags.
     */
    public function bulkDeleteUnused(): RedirectResponse
    {
        $deletedCount = BlogTag::where('usage_count', 0)->delete();

        // Clear blog cache
        $this->blogService->clearCache();

        return back()->with('success', "Deleted {$deletedCount} unused tags.");
    }

    /**
     * Refresh usage counts for all tags.
     */
    public function refreshUsageCounts(): RedirectResponse
    {
        $tags = BlogTag::all();
        
        foreach ($tags as $tag) {
            $tag->updateUsageCount();
        }

        // Clear blog cache
        $this->blogService->clearCache();

        return back()->with('success', 'Tag usage counts refreshed successfully.');
    }
}