<?php

namespace App\Features\Blog\Controllers\Admin;

use App\Features\Blog\Models\BlogPost;
use App\Features\Blog\Models\BlogCategory;
use App\Features\Blog\Models\BlogTag;
use App\Features\Blog\Services\BlogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogPostController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
        
        // Apply permission middleware
        $this->middleware('permission:blog.posts.view');
        $this->middleware('permission:blog.posts.create')->only(['create', 'store']);
        $this->middleware('permission:blog.posts.edit')->only(['edit', 'update']);
        $this->middleware('permission:blog.posts.delete')->only(['destroy']);
    }

    /**
     * Display a listing of blog posts.
     */
    public function index(Request $request): Response
    {
        $posts = BlogPost::with(['blogCategory', 'user', 'blogTags'])
            ->when($request->search, function ($query, $search) {
                return $query->search($search);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                return $query->where('blog_category_id', $category);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories = BlogCategory::active()->ordered()->get();
        $stats = $this->blogService->getBlogStats();

        return Inertia::render('admin/blog/posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'category']),
            'permissions' => [
                'canCreate' => auth()->user()->can('blog.posts.create'),
                'canEdit' => auth()->user()->can('blog.posts.edit'),
                'canDelete' => auth()->user()->can('blog.posts.delete'),
                'canPublish' => auth()->user()->can('blog.posts.edit'), // Using edit permission for publish
            ]
        ]);
    }

    /**
     * Show the form for creating a new blog post.
     */
    public function create(): Response
    {
        $categories = BlogCategory::active()->ordered()->get();
        $tags = BlogTag::orderBy('name')->get();

        return Inertia::render('admin/blog/posts/Create', [
            'categories' => $categories,
            'tags' => $tags,
            'seoSuggestions' => [], // No suggestions for new posts
            'config' => [
                'features' => config('blog.features'),
                'settings' => config('blog.settings'),
                'validation' => config('blog.validation'), // Include validation rules for frontend
            ]
        ]);
    }

    /**
     * Store a newly created blog post.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:' . config('blog.validation.title_max_length', 255),
            'slug' => 'nullable|string|max:' . config('blog.validation.slug_max_length', 255) . '|unique:blog_posts',
            'content' => 'nullable|string|max:' . config('blog.validation.content_max_length', 65535),
            'excerpt' => 'nullable|string|max:' . config('blog.validation.excerpt_max_length', 500),
            'status' => 'required|in:draft,published,scheduled',
            'is_featured' => 'boolean',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|string',
            'featured_image_alt' => 'nullable|string',
            'meta_title' => 'nullable|string|max:' . config('blog.validation.meta_title_max_length', 60),
            'meta_description' => 'nullable|string|max:' . config('blog.validation.meta_description_max_length', 160),
            'meta_keywords' => 'nullable|string',
            'schema_type' => 'nullable|string|in:' . implode(',', array_keys(config('blog.schema.available_types', ['BlogPosting']))),
            'topics' => 'nullable|array',
            'keywords' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'published_at' => 'nullable|date',
            // AEO Enhancement validation rules
            'faq_data' => 'nullable|array|max:10',
            'faq_data.*.question' => 'required_with:faq_data.*|string|min:10|max:300',
            'faq_data.*.answer' => 'required_with:faq_data.*|string|min:20|max:1000',
            'faq_data.*.id' => 'required_with:faq_data.*|string',
            'reading_time' => 'nullable|integer|min:1|max:120',
            'content_type' => 'nullable|string|in:blog_post,tutorial,review,news,guide,analysis',
            'content_score' => 'nullable|numeric|min:0|max:100',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set author
        $validated['user_id'] = Auth::id();

        // Handle published_at
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        // Sync tags if provided
        if (isset($validated['tags'])) {
            $post->syncTagsWithUsageCount($validated['tags']);
        }

        // Update category posts count
        if ($post->blogCategory) {
            $post->blogCategory->updatePostsCount();
        }

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    /**
     * Display the specified blog post.
     */
    public function show(BlogPost $post): Response
    {
        $post->load(['blogCategory', 'user', 'blogTags']);

        return Inertia::render('admin/blog/posts/Show', [
            'post' => $post
        ]);
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(BlogPost $post): Response
    {
        $post->load(['blogCategory', 'user', 'blogTags']);
        $categories = BlogCategory::active()->ordered()->get();
        $tags = BlogTag::orderBy('name')->get();

        // Generate SEO suggestions using BlogSeoService
        $seoSuggestions = [];
        try {
            $seoService = app(\App\Features\Blog\Services\BlogSeoService::class);
            $seoSuggestions = $seoService->optimizePostContent($post);
        } catch (\Exception $e) {
            logger('SEO analysis failed: ' . $e->getMessage());
        }

        return Inertia::render('admin/blog/posts/Edit', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'seoSuggestions' => $seoSuggestions,
            'config' => [
                'features' => config('blog.features'),
                'settings' => config('blog.settings'),
            ]
        ]);
    }

    /**
     * Update the specified blog post.
     */
    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:' . config('blog.validation.title_max_length', 255),
            'slug' => 'nullable|string|max:' . config('blog.validation.slug_max_length', 255) . '|unique:blog_posts,slug,' . $post->id,
            'content' => 'nullable|string|max:' . config('blog.validation.content_max_length', 65535),
            'excerpt' => 'nullable|string|max:' . config('blog.validation.excerpt_max_length', 500),
            'status' => 'required|in:draft,published,scheduled',
            'is_featured' => 'boolean',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|string',
            'featured_image_alt' => 'nullable|string',
            'meta_title' => 'nullable|string|max:' . config('blog.validation.meta_title_max_length', 60),
            'meta_description' => 'nullable|string|max:' . config('blog.validation.meta_description_max_length', 160),
            'meta_keywords' => 'nullable|string',
            'schema_type' => 'nullable|string|in:' . implode(',', array_keys(config('blog.schema.available_types', ['BlogPosting']))),
            'topics' => 'nullable|array',
            'keywords' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'published_at' => 'nullable|date',
            // AEO Enhancement validation rules
            'faq_data' => 'nullable|array|max:10',
            'faq_data.*.question' => 'required_with:faq_data.*|string|min:10|max:300',
            'faq_data.*.answer' => 'required_with:faq_data.*|string|min:20|max:1000',
            'faq_data.*.id' => 'required_with:faq_data.*|string',
            'reading_time' => 'nullable|integer|min:1|max:120',
            'content_type' => 'nullable|string|in:blog_post,tutorial,review,news,guide,analysis',
            'content_score' => 'nullable|numeric|min:0|max:100',
        ]);

        $oldCategoryId = $post->blog_category_id;

        // Handle status change to published
        if ($validated['status'] === 'published' && $post->status !== 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        // Sync tags if provided
        if (isset($validated['tags'])) {
            $post->syncTagsWithUsageCount($validated['tags']);
        }

        // Update category posts counts
        if ($oldCategoryId && $oldCategoryId !== $post->blog_category_id) {
            BlogCategory::find($oldCategoryId)?->updatePostsCount();
        }
        if ($post->blogCategory) {
            $post->blogCategory->updatePostsCount();
        }

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified blog post.
     */
    public function destroy(BlogPost $post): RedirectResponse
    {
        $categoryId = $post->blog_category_id;
        
        $post->delete();

        // Update category posts count
        if ($categoryId) {
            BlogCategory::find($categoryId)?->updatePostsCount();
        }

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.posts.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    /**
     * Publish a blog post.
     */
    public function publish(BlogPost $post): RedirectResponse
    {
        $post->publish();
        $this->blogService->clearCache();

        return back()->with('success', 'Blog post published successfully.');
    }

    /**
     * Unpublish a blog post.
     */
    public function unpublish(BlogPost $post): RedirectResponse
    {
        $post->unpublish();
        $this->blogService->clearCache();

        return back()->with('success', 'Blog post unpublished successfully.');
    }

    /**
     * Toggle featured status of a blog post.
     */
    public function toggleFeatured(BlogPost $post): RedirectResponse
    {
        $post->update(['is_featured' => !$post->is_featured]);
        $this->blogService->clearCache();

        $status = $post->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Blog post marked as {$status}.");
    }
}