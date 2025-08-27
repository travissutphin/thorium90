<?php

namespace App\Features\Blog\Controllers;

use App\Features\Blog\Models\BlogPost;
use App\Features\Blog\Models\BlogCategory;
use App\Features\Blog\Models\BlogTag;
use App\Features\Blog\Services\BlogService;
use App\Features\Blog\Services\BlogSeoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    protected BlogService $blogService;
    protected BlogSeoService $blogSeoService;

    public function __construct(BlogService $blogService, BlogSeoService $blogSeoService)
    {
        $this->blogService = $blogService;
        $this->blogSeoService = $blogSeoService;
    }

    /**
     * Display the blog index page.
     */
    public function index(Request $request): View
    {
        // Add search functionality if query present
        $searchQuery = $request->get('search', '');
        
        if ($searchQuery) {
            $posts = $this->blogService->searchPosts($searchQuery);
        } else {
            $posts = $this->blogService->getPublishedPosts();
        }
        
        $featuredPosts = $this->blogService->getFeaturedPosts();
        $categories = $this->blogService->getActiveCategories();
        $popularTags = $this->blogService->getPopularTags();
        $recentPosts = $this->blogService->getRecentPosts();

        // Generate structured data for the listing page
        $structuredData = $this->blogSeoService->generateListingSchema($posts, 'blog');

        // Generate OpenGraph meta tags
        $openGraphMeta = [
            'og:title' => 'Blog - ' . config('app.name'),
            'og:description' => 'Discover the latest insights, tips, and stories from ' . config('app.name'),
            'og:url' => route('blog.index'),
            'og:type' => 'website',
        ];

        return view('blog::index', compact(
            'posts',
            'featuredPosts',
            'categories',
            'popularTags',
            'recentPosts',
            'structuredData',
            'openGraphMeta',
            'searchQuery'
        ));
    }

    /**
     * Display a specific blog post.
     */
    public function show(string $slug): View
    {
        $post = BlogPost::where('slug', $slug)
            ->published()
            ->with(['blogCategory', 'user', 'blogTags'])
            ->firstOrFail();

        // Increment view count if enabled
        $post->incrementViews();

        // Get related posts (eager loading now handled in the model)
        $relatedPosts = $post->relatedPosts(4);

        // Get previous and next posts
        $previousPost = $post->previousPost();
        $nextPost = $post->nextPost();

        // Get comments if enabled
        $comments = collect();
        if (config('blog.features.comments')) {
            $comments = $post->approvedComments()->with(['user', 'replies.user'])->get();
        }

        // Generate enhanced structured data for this specific post
        $enhancedSchema = $this->blogSeoService->generatePostSchema($post, [
            'relatedPosts' => $relatedPosts,
            'comments' => $comments,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
        ]);

        // Generate breadcrumbs with schema markup
        $breadcrumbs = $this->blogSeoService->generateBreadcrumbs('post', $post);
        $breadcrumbSchema = $this->blogSeoService->generateBreadcrumbSchema($breadcrumbs);

        // Generate OpenGraph meta tags
        $openGraphMeta = $this->blogSeoService->generateOpenGraphMeta($post);

        return view('blog::post', compact(
            'post',
            'relatedPosts',
            'previousPost',
            'nextPost',
            'comments',
            'breadcrumbs',
            'breadcrumbSchema',
            'openGraphMeta'
        ))->with('structuredData', $enhancedSchema);
    }

    /**
     * Display posts by category.
     */
    public function category(string $slug): View
    {
        $category = BlogCategory::where('slug', $slug)->active()->firstOrFail();
        $posts = $this->blogService->getPostsByCategory($category);

        // Get sidebar data (required by category.blade.php sidebar partial)
        $categories = $this->blogService->getActiveCategories();
        $popularTags = $this->blogService->getPopularTags();
        $recentPosts = $this->blogService->getRecentPosts();

        // Generate breadcrumbs
        $breadcrumbs = $this->blogSeoService->generateBreadcrumbs('category', $category);

        // Generate structured data
        $structuredData = $this->blogSeoService->generateListingSchema($posts, 'category');

        // Generate OpenGraph meta tags
        $openGraphMeta = $this->blogSeoService->generateOpenGraphMeta($category, 'category');

        return view('blog::category', compact(
            'category',
            'posts',
            'categories',
            'popularTags',
            'recentPosts',
            'breadcrumbs',
            'structuredData',
            'openGraphMeta'
        ));
    }

    /**
     * Display posts by tag.
     */
    public function tag(string $slug): View
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();
        $posts = $this->blogService->getPostsByTag($tag);

        // Get sidebar data (required by tag.blade.php sidebar partial)
        $categories = $this->blogService->getActiveCategories();
        $popularTags = $this->blogService->getPopularTags();
        $recentPosts = $this->blogService->getRecentPosts();

        // Generate breadcrumbs
        $breadcrumbs = $this->blogSeoService->generateBreadcrumbs('tag', $tag);

        // Generate structured data
        $structuredData = $this->blogSeoService->generateListingSchema($posts, 'tag');

        // Generate OpenGraph meta tags
        $openGraphMeta = $this->blogSeoService->generateOpenGraphMeta($tag, 'tag');

        return view('blog::tag', compact(
            'tag',
            'posts',
            'categories',
            'popularTags',
            'recentPosts',
            'breadcrumbs',
            'structuredData',
            'openGraphMeta'
        ));
    }

    /**
     * Search blog posts.
     */
    public function search(Request $request): View
    {
        $query = $request->get('q', '');
        $posts = collect();

        if ($query) {
            $posts = $this->blogService->searchPosts($query);
        }

        // Get sidebar data (required by search.blade.php sidebar partial)
        $categories = $this->blogService->getActiveCategories();
        $popularTags = $this->blogService->getPopularTags();
        $recentPosts = $this->blogService->getRecentPosts();

        // Generate structured data
        $structuredData = $this->blogSeoService->generateListingSchema($posts, 'search');

        return view('blog::search', compact(
            'posts',
            'query',
            'categories',
            'popularTags',
            'recentPosts',
            'structuredData'
        ));
    }

    /**
     * Display blog archive by year/month.
     */
    public function archive(Request $request): View
    {
        $year = $request->get('year');
        $month = $request->get('month');

        $posts = BlogPost::published()
            ->when($year, function ($query) use ($year) {
                return $query->whereYear('published_at', $year);
            })
            ->when($month, function ($query) use ($month) {
                return $query->whereMonth('published_at', $month);
            })
            ->with(['blogCategory', 'user', 'blogTags'])
            ->orderBy('published_at', 'desc')
            ->paginate(config('blog.settings.posts_per_page', 12));

        $archiveData = $this->blogService->getArchiveData();

        return view('blog::archive', compact(
            'posts',
            'archiveData',
            'year',
            'month'
        ));
    }
}