@extends('blog::layouts.blog-layout', [
    'title' => 'Search' . ($query ? ' - ' . $query : '') . ' - Blog',
    'metaTitle' => 'Search Blog Posts' . ($query ? ' - ' . $query : '') . ' - ' . config('app.name'),
    'metaDescription' => 'Search through our blog posts' . ($query ? ' for "' . $query . '"' : '') . ' and discover relevant content.',
    'structuredData' => $structuredData ?? null,
    'openGraphMeta' => $openGraphMeta ?? null,
    'breadcrumbs' => $breadcrumbs ?? null
])

@section('content')
<div class="blog-container">
    <!-- Search Header -->
    <header class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 bg-gradient-to-br from-green-500 to-teal-600">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        
        @if($query)
            <h1 class="blog-title-large gradient-text mb-2">
                Search Results for "{{ $query }}"
            </h1>
            @if($posts->total() > 0)
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    Found {{ $posts->total() }} {{ Str::plural('result', $posts->total()) }}
                </p>
            @else
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                    No results found for your search
                </p>
            @endif
        @else
            <h1 class="blog-title-large gradient-text mb-2">Search Blog Posts</h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                Find the content you're looking for
            </p>
        @endif
    </header>

    <!-- Search Form -->
    <div class="max-w-2xl mx-auto mb-12">
        <form method="GET" action="{{ route('blog.search') }}" class="blog-search">
            <svg class="blog-search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
                type="text" 
                name="q"
                value="{{ $query }}"
                placeholder="Search blog posts, categories, tags..."
                class="w-full"
                autofocus
            >
            <button type="submit" class="blog-search-button">
                Search
            </button>
        </form>

        @if($query)
        <!-- Search Filters -->
        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('blog.search', ['q' => $query]) }}" 
               class="text-sm px-4 py-2 rounded-full {{ !request('type') ? 'bg-purple-100 text-purple-800' : 'text-gray-600 hover:bg-gray-100' }} transition-colors">
                All Results ({{ $posts->total() }})
            </a>
            @if($categoryResults > 0)
            <a href="{{ route('blog.search', ['q' => $query, 'type' => 'category']) }}" 
               class="text-sm px-4 py-2 rounded-full {{ request('type') == 'category' ? 'bg-purple-100 text-purple-800' : 'text-gray-600 hover:bg-gray-100' }} transition-colors">
                Categories ({{ $categoryResults }})
            </a>
            @endif
            @if($tagResults > 0)
            <a href="{{ route('blog.search', ['q' => $query, 'type' => 'tag']) }}" 
               class="text-sm px-4 py-2 rounded-full {{ request('type') == 'tag' ? 'bg-purple-100 text-purple-800' : 'text-gray-600 hover:bg-gray-100' }} transition-colors">
                Tags ({{ $tagResults }})
            </a>
            @endif
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3">
            @if($query)
                @if($posts->count() > 0)
                    <!-- Search Stats -->
                    <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ $posts->firstItem() }}-{{ $posts->lastItem() }} of {{ $posts->total() }} results
                        @if(request('type'))
                            in {{ ucfirst(request('type')) }}
                        @endif
                    </div>

                    <!-- Results -->
                    <div class="space-y-8">
                        @foreach($posts as $post)
                            @include('blog::partials.post-card-horizontal', ['post' => $post])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="blog-pagination">
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4 bg-gray-100">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                            No results found for "{{ $query }}"
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Try adjusting your search terms or browse our latest posts below.
                        </p>

                        <!-- Search Suggestions -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Search suggestions:</h4>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Check your spelling</li>
                                <li>• Try more general keywords</li>
                                <li>• Use fewer keywords</li>
                                <li>• Browse categories or tags below</li>
                            </ul>
                        </div>

                        <div class="space-x-4">
                            <a href="{{ route('blog.index') }}" class="blog-btn-primary">
                                Browse All Posts
                            </a>
                            <a href="{{ route('blog.search') }}" class="blog-btn-secondary">
                                New Search
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <!-- Popular Searches / Categories -->
                @if($categories && $categories->count() > 0)
                <div class="mb-12">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Browse by Category</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($categories as $category)
                        <a href="{{ route('blog.categories.show', $category->slug) }}" 
                           class="flex items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-purple-300 dark:hover:border-purple-600 transition-colors group">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-4"
                                 style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7l2 2-2 2M5 6l2-2-2-2m0 12l2 2-2 2m14-2l-2 2 2 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                    {{ $category->name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                                </p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($popularTags && $popularTags->count() > 0)
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Popular Tags</h2>
                    <div class="flex flex-wrap gap-3">
                        @foreach($popularTags as $tag)
                            <a href="{{ route('blog.tags.show', $tag->slug) }}" 
                               class="blog-tag text-sm"
                               style="{{ $tag->css_color }}">
                                #{{ $tag->name }}
                                <span class="ml-1 text-xs opacity-75">({{ $tag->usage_count }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            @include('blog::partials.sidebar', [
                'categories' => $categories,
                'popularTags' => $popularTags,
                'recentPosts' => $recentPosts ?? collect()
            ])
        </div>
    </div>
</div>
@endsection