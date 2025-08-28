@extends('blog::layouts.blog-layout', [
    'title' => $category->name . ' - Blog',
    'metaTitle' => ($category->meta_title ?: $category->name . ' - ' . config('app.name') . ' Blog'),
    'metaDescription' => ($category->meta_description ?: 'Browse posts in the ' . $category->name . ' category on ' . config('app.name') . ' blog.'),
    'metaKeywords' => $category->meta_keywords,
    'structuredData' => $structuredData ?? null,
    'openGraphMeta' => $openGraphMeta ?? null,
    'breadcrumbs' => $breadcrumbs ?? null
])

@section('content')
<div class="blog-container">
    <!-- Category Header -->
    <header class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
             style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7l2 2-2 2M5 6l2-2-2-2m0 12l2 2-2 2m14-2l-2 2 2 2"></path>
            </svg>
        </div>
        
        <h1 class="blog-title-large gradient-text mb-2">{{ $category->name }}</h1>
        
        @if($category->description)
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-4">
            {{ $category->description }}
        </p>
        @endif
        
        <div class="flex items-center justify-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7l2 2-2 2M5 6l2-2-2-2m0 12l2 2-2 2m14-2l-2 2 2 2"></path>
                </svg>
                {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
            </span>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3">
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
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4"
                         style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        No posts in {{ $category->name }} yet
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Check back soon for new content in this category!
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