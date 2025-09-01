{{-- Blog Post Card Component --}}
<article class="blog-card {{ isset($featured) && $featured ? 'blog-card-featured' : '' }}">
    <!-- Featured Image -->
    @if($post->featured_image)
    <div class="mb-4">
        <a href="{{ $post->url }}">
            <img src="{{ $post->featured_image }}" 
                 alt="{{ $post->featured_image_alt ?: $post->title }}"
                 class="blog-featured-image hover:opacity-90 transition-opacity">
        </a>
    </div>
    @endif

    <!-- Category -->
    @if($post->blogCategory)
    <div class="mb-3">
        <a href="{{ route('blog.categories.show', $post->blogCategory->slug) }}" 
           class="blog-category-tag text-xs"
           style="{{ $post->blogCategory->css_color }}">
            {{ $post->blogCategory->name }}
        </a>
    </div>
    @endif

    <!-- Title -->
    <h2 class="blog-title mb-3">
        <a href="{{ $post->url }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            {{ $post->title }}
        </a>
    </h2>

    <!-- Excerpt -->
    @if($post->excerpt)
    <div class="blog-excerpt mb-4">
        {{ $post->excerpt }}
    </div>
    @endif

    <!-- Meta Information -->
    <div class="blog-meta mb-4">
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
                {{ $post->published_at->format('M j, Y') }}
            </time>
        </div>

        @if($post->reading_time)
        <div class="blog-meta-separator"></div>
        
        <!-- Reading Time -->
        <div class="blog-meta-item">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ $post->reading_time }} min</span>
        </div>
        @endif
    </div>

    <!-- Tags -->
    @if($post->blogTags && $post->blogTags->count() > 0)
    <div class="blog-tags-list mb-4">
        @foreach($post->blogTags->take(3) as $tag)
            <a href="{{ route('blog.tags.show', $tag->slug) }}" 
               class="blog-tag"
               style="{{ $tag->css_color }}">
                #{{ $tag->name }}
            </a>
        @endforeach
        @if($post->blogTags->count() > 3)
            <span class="blog-tag">+{{ $post->blogTags->count() - 3 }} more</span>
        @endif
    </div>
    @endif

    <!-- Read More Button -->
    <div class="mt-4">
        <a href="{{ $post->url }}" class="blog-btn-primary text-sm">
            Read More
            <svg class="w-4 h-4 ml-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>

    <!-- View Count (if enabled) -->
    @if(config('blog.features.view_counts') && $post->view_count > 0)
    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        {{ number_format($post->view_count) }} views
    </div>
    @endif
</article>