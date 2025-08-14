<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     */
    public function index(Request $request)
    {
        $this->authorize('view pages');

        $query = Page::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->featured !== null, function ($query) use ($request) {
                $query->where('is_featured', $request->boolean('featured'));
            });

        // Apply user-specific filters based on permissions
        if (!Auth::user()->can('edit pages')) {
            $query->where('user_id', Auth::id());
        }

        $pages = $query->orderBy('created_at', 'desc')
                      ->paginate(15)
                      ->withQueryString();

        $stats = [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'drafts' => Page::where('status', 'draft')->count(),
            'featured' => Page::where('is_featured', true)->count(),
        ];

        return Inertia::render('content/pages/index', [
            'pages' => $pages,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'featured']),
        ]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        $this->authorize('create pages');

        return Inertia::render('content/pages/create', [
            'schemaTypes' => $this->getSchemaTypes(),
        ]);
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create pages');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,private',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'schema_type' => 'nullable|string|in:WebPage,Article,BlogPosting,NewsArticle',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Page::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Set published_at if status is published
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $validated['user_id'] = Auth::id();

        $page = Page::create($validated);

        return redirect()->route('content.pages.index')
                        ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        // Check if user can view this page
        if ($page->status === 'private' && $page->user_id !== Auth::id()) {
            $this->authorize('edit pages');
        }

        if ($page->status === 'draft' && $page->user_id !== Auth::id()) {
            $this->authorize('edit pages');
        }

        $page->load('user');

        // Explicitly include computed properties and ensure all data is properly serialized
        $pageData = $page->toArray();
        $pageData['reading_time'] = $page->reading_time;
        $pageData['url'] = $page->url;
        $pageData['full_meta_title'] = $page->full_meta_title;
        
        // Ensure user relationship is properly included
        $pageData['user'] = $page->user->only(['id', 'name']);

        return Inertia::render('content/pages/show', [
            'page' => $pageData,
            'schemaData' => $page->schema_data,
        ]);
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        // Check permissions
        if ($page->user_id === Auth::id()) {
            $this->authorize('edit own pages');
        } else {
            $this->authorize('edit pages');
        }

        return Inertia::render('content/pages/edit', [
            'page' => $page,
            'schemaTypes' => $this->getSchemaTypes(),
        ]);
    }

    /**
     * Update the specified page in storage.
     */
    public function update(Request $request, Page $page)
    {
        // Check permissions
        if ($page->user_id === Auth::id()) {
            $this->authorize('edit own pages');
        } else {
            $this->authorize('edit pages');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,private',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'schema_type' => 'nullable|string|in:WebPage,Article,BlogPosting,NewsArticle',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure slug is unique (excluding current page)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Page::where('slug', $validated['slug'])->where('id', '!=', $page->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle published_at timestamp
        if ($validated['status'] === 'published' && !$page->published_at) {
            $validated['published_at'] = now();
        } elseif ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
        }

        $page->update($validated);

        return redirect()->route('content.pages.index')
                        ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page from storage.
     */
    public function destroy(Page $page)
    {
        // Check permissions
        if ($page->user_id === Auth::id()) {
            $this->authorize('delete own pages');
        } else {
            $this->authorize('delete pages');
        }

        $page->delete();

        return redirect()->route('content.pages.index')
                        ->with('success', 'Page deleted successfully.');
    }

    /**
     * Publish the specified page.
     */
    public function publish(Page $page)
    {
        // Check permissions
        if ($page->user_id === Auth::id()) {
            $this->authorize('edit own pages');
        } else {
            $this->authorize('publish pages');
        }

        $page->publish();

        return back()->with('success', 'Page published successfully.');
    }

    /**
     * Unpublish the specified page.
     */
    public function unpublish(Page $page)
    {
        // Check permissions
        if ($page->user_id === Auth::id()) {
            $this->authorize('edit own pages');
        } else {
            $this->authorize('publish pages');
        }

        $page->unpublish();

        return back()->with('success', 'Page unpublished successfully.');
    }

    /**
     * Bulk actions for pages.
     */
    public function bulkAction(Request $request)
    {
        $this->authorize('edit pages');

        $validated = $request->validate([
            'action' => 'required|in:publish,unpublish,delete,feature,unfeature',
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:pages,id',
        ]);

        $pages = Page::whereIn('id', $validated['page_ids'])->get();

        foreach ($pages as $page) {
            switch ($validated['action']) {
                case 'publish':
                    $page->publish();
                    break;
                case 'unpublish':
                    $page->unpublish();
                    break;
                case 'delete':
                    $page->delete();
                    break;
                case 'feature':
                    $page->update(['is_featured' => true]);
                    break;
                case 'unfeature':
                    $page->update(['is_featured' => false]);
                    break;
            }
        }

        $actionName = ucfirst($validated['action']);
        return back()->with('success', "{$actionName} action completed for " . count($pages) . " pages.");
    }

    /**
     * Generate sitemap for pages.
     */
    public function sitemap()
    {
        $pages = Page::published()
                    ->select(['slug', 'updated_at', 'created_at'])
                    ->orderBy('updated_at', 'desc')
                    ->get();

        return response()->view('sitemap.pages', compact('pages'))
                        ->header('Content-Type', 'application/xml');
    }

    /**
     * Check if a slug is available.
     */
    public function checkSlug(Request $request)
    {
        $slug = $request->input('slug');
        $excludeId = $request->input('exclude_id');
        
        if (empty($slug)) {
            return response()->json(['available' => false, 'message' => 'Slug cannot be empty']);
        }
        
        // Generate proper slug format
        $formattedSlug = Str::slug($slug);
        
        if (empty($formattedSlug)) {
            return response()->json(['available' => false, 'message' => 'Invalid slug format']);
        }
        
        // Check if slug exists (excluding current page if editing)
        $query = Page::where('slug', $formattedSlug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        if ($exists) {
            // Generate unique slug suggestion
            $originalSlug = $formattedSlug;
            $counter = 1;
            while (Page::where('slug', $formattedSlug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
                $formattedSlug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            return response()->json([
                'available' => false,
                'message' => 'Slug already exists',
                'suggestion' => $formattedSlug,
                'formatted' => $formattedSlug
            ]);
        }
        
        return response()->json([
            'available' => true,
            'message' => 'Slug is available',
            'formatted' => $formattedSlug
        ]);
    }

    /**
     * Get available schema types.
     */
    private function getSchemaTypes()
    {
        return [
            'WebPage' => 'Web Page (Default)',
            'Article' => 'Article',
            'BlogPosting' => 'Blog Post',
            'NewsArticle' => 'News Article',
        ];
    }
}
