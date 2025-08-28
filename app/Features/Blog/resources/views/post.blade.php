@extends('blog::layouts.blog-layout', [
    'title' => $post->title,
    'metaTitle' => $post->meta_title ?: $post->title,
    'metaDescription' => $post->meta_description ?: $post->excerpt,
    'metaKeywords' => $post->meta_keywords,
    'structuredData' => $post->schema_data,
    'openGraphMeta' => $openGraphMeta ?? null,
    'breadcrumbs' => $breadcrumbs ?? null
])

@section('content')
<div class="blog-container">
    <div class="blog-content-wrapper">
        <div class="blog-desktop-layout">
        <!-- Main Content -->
        <article class="blog-content-padding">
            <!-- Post Header -->
            <header class="mb-8">
                <!-- Category -->
                @if($post->blogCategory)
                <div class="mb-4">
                    <a href="{{ route('blog.categories.show', $post->blogCategory->slug) }}" 
                       class="blog-category-tag"
                       style="{{ $post->blogCategory->css_color }}">
                        {{ $post->blogCategory->name }}
                    </a>
                </div>
                @endif

                <!-- Title -->
                <h1 class="blog-title-large mb-4">{{ $post->title }}</h1>

                <!-- Meta Information -->
                <div class="blog-meta mb-6">
                    <!-- Author -->
                    <div class="blog-meta-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ $post->user->name }}</span>
                    </div>

                    <div class="blog-meta-separator"></div>

                    <!-- Published Date -->
                    <div class="blog-meta-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <time datetime="{{ $post->published_at->toISOString() }}">
                            {{ $post->published_at->format('F j, Y') }}
                        </time>
                    </div>

                    <div class="blog-meta-separator"></div>

                    <!-- Reading Time -->
                    @if($post->reading_time)
                    <div class="blog-meta-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $post->reading_time }} min read</span>
                    </div>
                    @endif

                    @if(config('blog.features.view_counts'))
                    <div class="blog-meta-separator"></div>
                    
                    <!-- View Count -->
                    <div class="blog-meta-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>{{ number_format($post->view_count) }} views</span>
                    </div>
                    @endif
                </div>

                <!-- Tags -->
                @if($post->blogTags && $post->blogTags->count() > 0)
                <div class="blog-tags-list">
                    @foreach($post->blogTags as $tag)
                        <a href="{{ route('blog.tags.show', $tag->slug) }}" 
                           class="blog-tag"
                           style="{{ $tag->css_color }}">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
                @endif
            </header>

            <!-- Featured Image -->
            @if($post->featured_image)
            <div class="mb-8">
                <img src="{{ asset('storage/' . $post->featured_image) }}" 
                     alt="{{ $post->featured_image_alt ?: $post->title }}"
                     class="blog-featured-image-large">
            </div>
            @endif

            <!-- Post Content -->
            <div class="blog-prose mb-8">
                {!! $post->content !!}
            </div>

            <!-- Social Sharing -->
            <div class="flex items-center justify-between py-6 border-t border-b border-gray-200 dark:border-gray-700 mb-8">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Share:</span>
                    
                    <button onclick="shareBlogPost('{{ $post->url }}', '{{ addslashes($post->title) }}', 'twitter')"
                            class="p-2 text-gray-500 hover:text-blue-500 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </button>

                    <button onclick="shareBlogPost('{{ $post->url }}', '{{ addslashes($post->title) }}', 'facebook')"
                            class="p-2 text-gray-500 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </button>

                    <button onclick="shareBlogPost('{{ $post->url }}', '{{ addslashes($post->title) }}', 'linkedin')"
                            class="p-2 text-gray-500 hover:text-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </button>

                    <button onclick="copyBlogLink('{{ $post->url }}')"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Post Navigation -->
            <div class="flex justify-between items-center mb-12">
                @if($previousPost)
                <a href="{{ $previousPost->url }}" 
                   class="flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <div>
                        <div class="text-xs uppercase tracking-wider mb-1">Previous</div>
                        <div class="font-medium">{{ Str::limit($previousPost->title, 40) }}</div>
                    </div>
                </a>
                @else
                <div></div>
                @endif

                @if($nextPost)
                <a href="{{ $nextPost->url }}" 
                   class="flex items-center text-right text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors group">
                    <div>
                        <div class="text-xs uppercase tracking-wider mb-1">Next</div>
                        <div class="font-medium">{{ Str::limit($nextPost->title, 40) }}</div>
                    </div>
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @endif
            </div>

            <!-- Related Posts -->
            @if($relatedPosts && $relatedPosts->count() > 0)
            <section class="mb-12">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Related Posts</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($relatedPosts as $relatedPost)
                        @include('blog::partials.post-card', ['post' => $relatedPost])
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Comments Section -->
            @if(config('blog.features.comments') && $comments)
            <section class="blog-comments">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">
                    Comments ({{ $comments->count() }})
                </h3>

                @if($comments->count() > 0)
                    <div class="space-y-6">
                        @foreach($comments as $comment)
                            @include('blog::partials.comment', ['comment' => $comment])
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400 text-center py-8">
                        No comments yet. Be the first to share your thoughts!
                    </p>
                @endif
            </section>
            @endif
        </article>

        <!-- Sidebar -->
        <div class="blog-sidebar-container">
            @include('blog::partials.post-sidebar', [
                'post' => $post,
                'categories' => $categories ?? collect(),
                'popularTags' => $popularTags ?? collect(),
                'recentPosts' => $recentPosts ?? collect()
            ])
        </div>
    </div>
    </div>
</div>
@endsection