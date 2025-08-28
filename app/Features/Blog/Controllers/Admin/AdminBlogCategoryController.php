<?php

namespace App\Features\Blog\Controllers\Admin;

use App\Features\Blog\Models\BlogCategory;
use App\Features\Blog\Services\BlogService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogCategoryController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
        
        // Apply permission middleware
        $this->middleware('permission:blog.categories.view');
        $this->middleware('permission:blog.categories.create')->only(['create', 'store']);
        $this->middleware('permission:blog.categories.edit')->only(['edit', 'update']);
        $this->middleware('permission:blog.categories.delete')->only(['destroy']);
    }

    /**
     * Display a listing of blog categories.
     */
    public function index(): Response
    {
        $categories = BlogCategory::withCount(['blogPosts'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/blog/categories/Index', [
            'categories' => $categories,
            'permissions' => [
                'canManage' => auth()->user()->can('manage blog categories'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('admin/blog/categories/Create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:' . config('blog.validation.category_name_max_length', 100),
            'slug' => 'nullable|string|max:' . config('blog.validation.slug_max_length', 255) . '|unique:blog_categories',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'meta_title' => 'nullable|string|max:' . config('blog.validation.meta_title_max_length', 60),
            'meta_description' => 'nullable|string|max:' . config('blog.validation.meta_description_max_length', 160),
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set default color if not provided
        if (empty($validated['color'])) {
            $validated['color'] = '#e91e63';
        }

        BlogCategory::create($validated);

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Blog category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(BlogCategory $category): Response
    {
        $category->load(['blogPosts' => function($query) {
            $query->with(['user', 'blogTags'])->latest('created_at');
        }]);

        return Inertia::render('admin/blog/categories/Show', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(BlogCategory $category): Response
    {
        return Inertia::render('admin/blog/categories/Edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, BlogCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:' . config('blog.validation.category_name_max_length', 100),
            'slug' => 'nullable|string|max:' . config('blog.validation.slug_max_length', 255) . '|unique:blog_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'meta_title' => 'nullable|string|max:' . config('blog.validation.meta_title_max_length', 60),
            'meta_description' => 'nullable|string|max:' . config('blog.validation.meta_description_max_length', 160),
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Blog category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(BlogCategory $category): RedirectResponse
    {
        // Check if category has posts
        if ($category->blogPosts()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing posts. Move posts to another category first.');
        }

        $category->delete();

        // Clear blog cache
        $this->blogService->clearCache();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Blog category deleted successfully.');
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggleActive(BlogCategory $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        // Clear blog cache
        $this->blogService->clearCache();

        $status = $category->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Category {$status} successfully.");
    }

    /**
     * Update category sort order.
     */
    public function updateOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:blog_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['categories'] as $categoryData) {
            BlogCategory::where('id', $categoryData['id'])
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        // Clear blog cache
        $this->blogService->clearCache();

        return back()->with('success', 'Category order updated successfully.');
    }
}