@extends('blog::layouts.blog-layout', [
    'title' => '#' . $tag->name . ' - Blog',
    'metaTitle' => ($tag->meta_title ?: '#' . $tag->name . ' - ' . config('app.name') . ' Blog'),
    'metaDescription' => ($tag->meta_description ?: 'Browse posts tagged with #' . $tag->name . ' on ' . config('app.name') . ' blog.'),
    'metaKeywords' => $tag->meta_keywords,
    'structuredData' => $structuredData ?? null,
    'openGraphMeta' => $openGraphMeta ?? null,
    'breadcrumbs' => $breadcrumbs ?? null
])

@section('content')
<div class="blog-container">
    <!-- Tag Header -->
    <header class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 bg-gradient-to-br from-purple-500 to-blue-600">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
            </svg>
        </div>
        
        <h1 class="blog-title-large gradient-text mb-2">#{{ $tag->name }}</h1>
        
        @if($tag->description)
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-4">
            {{ $tag->description }}
        </p>
        @endif
        
        <div class="flex items-center justify-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                </svg>
                {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
            </span>
            @if($tag->usage_count > 0)
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                {{ $tag->usage_count }} total uses
            </span>
            @endif
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3">
            @if($posts->count() > 0)
                <!-- Related Tags (if any) -->
                @if($relatedTags && $relatedTags->count() > 0)
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Related Tags</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($relatedTags as $relatedTag)
                            <a href="{{ route('blog.tags.show', $relatedTag->slug) }}" 
                               class="blog-tag"
                               style="{{ $relatedTag->css_color }}">
                                #{{ $relatedTag->name }}
                                <span class="ml-1 text-xs opacity-75">({{ $relatedTag->usage_count }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Posts -->
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
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4 bg-gradient-to-br from-purple-500 to-blue-600">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        No posts tagged with #{{ $tag->name }} yet
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Check back soon for new content with this tag!
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('blog.index') }}" class="blog-btn-secondary">
                            Browse All Posts
                        </a>
                    </div>
                </div>
            @endif
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