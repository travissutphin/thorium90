@extends('blog::layouts.blog-layout', [
    'title' => 'Blog',
    'metaTitle' => 'Blog - Latest Insights and Stories',
    'metaDescription' => 'Discover the latest insights, tips, and stories from ' . config('app.name') . '. Stay updated with our blog posts.',
    'structuredData' => $structuredData ?? null,
    'openGraphMeta' => $openGraphMeta ?? null
])

@section('content')
<div class="blog-container">
    <!-- Blog Header -->
    <header class="text-center mb-12">
        <h1 class="blog-title-large gradient-text">
            {{ config('app.name') }} Blog
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
            Discover the latest insights, tips, and stories from our team. 
            Stay updated with industry trends and expert advice.
        </p>
    </header>

    <!-- Blog Search -->
    <div class="max-w-md mx-auto mb-12">
        <div class="blog-search">
            <svg class="blog-search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
                type="text" 
                placeholder="Search blog posts..."
                class="w-full"
            >
        </div>
    </div>

    <!-- Featured Posts Section -->
    @if($featuredPosts && $featuredPosts->count() > 0)
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-8 flex items-center">
            <svg class="w-6 h-6 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            Featured Posts
        </h2>
        <div class="blog-grid">
            @foreach($featuredPosts as $post)
                @include('blog::partials.post-card', ['post' => $post, 'featured' => true])
            @endforeach
        </div>
    </section>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3">
            <!-- Latest Posts -->
            <section>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-8">Latest Posts</h2>
                
                @if($posts->count() > 0)
                    <div class="space-y-8">
                        @foreach($posts as $post)
                            @include('blog::partials.post-card-horizontal', ['post' => $post])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="blog-pagination">
                        {{ $posts->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No posts yet</h3>
                        <p class="text-gray-600 dark:text-gray-400">Check back soon for our latest content!</p>
                    </div>
                @endif
            </section>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            @include('blog::partials.sidebar', [
                'categories' => $categories,
                'popularTags' => $popularTags,
                'recentPosts' => $recentPosts
            ])
        </div>
    </div>
</div>
@endsection