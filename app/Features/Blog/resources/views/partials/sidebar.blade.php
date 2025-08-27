{{-- Blog Sidebar Component --}}
<aside class="space-y-8">
    <!-- Categories Widget -->
    @if($categories && $categories->count() > 0)
    <div class="blog-sidebar">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Categories</h3>
        <ul class="space-y-2">
            @foreach($categories as $category)
            <li>
                <a href="{{ route('blog.categories.show', $category->slug) }}" 
                   class="flex items-center justify-between text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors py-2">
                    <span class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-3" 
                             style="background-color: {{ $category->color }}"></div>
                        {{ $category->name }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-500">
                        {{ $category->posts_count }}
                    </span>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Popular Tags Widget -->
    @if($popularTags && $popularTags->count() > 0)
    <div class="blog-sidebar">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Popular Tags</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($popularTags as $tag)
                <a href="{{ route('blog.tags.show', $tag->slug) }}" 
                   class="blog-tag"
                   style="{{ $tag->css_color }}">
                    #{{ $tag->name }}
                    <span class="ml-1 text-xs opacity-75">({{ $tag->usage_count }})</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Posts Widget -->
    @if($recentPosts && $recentPosts->count() > 0)
    <div class="blog-sidebar">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Recent Posts</h3>
        <div class="space-y-4">
            @foreach($recentPosts as $post)
            <article class="group">
                <div class="flex space-x-3">
                    @if($post->featured_image)
                    <div class="flex-shrink-0">
                        <a href="{{ $post->url }}">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                 alt="{{ $post->featured_image_alt ?: $post->title }}"
                                 class="w-16 h-16 object-cover rounded-lg group-hover:opacity-90 transition-opacity">
                        </a>
                    </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors line-clamp-2">
                            <a href="{{ $post->url }}">
                                {{ $post->title }}
                            </a>
                        </h4>
                        
                        <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                            <time datetime="{{ $post->published_at->toISOString() }}">
                                {{ $post->published_at->format('M j, Y') }}
                            </time>
                            @if($post->reading_time)
                                <span class="mx-1">•</span>
                                <span>{{ $post->reading_time }} min</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Newsletter Signup Widget -->
    <div class="blog-sidebar">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Stay Updated</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Subscribe to our newsletter to get the latest posts delivered straight to your inbox.
        </p>
        <form class="space-y-3" method="POST" action="{{ route('newsletter.subscribe') }}" onsubmit="return false;">
            @csrf
            <input type="email" 
                   name="email" 
                   placeholder="Enter your email"
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-800 dark:text-gray-100">
            <button type="submit" 
                    class="blog-btn-primary w-full text-sm">
                Subscribe
            </button>
        </form>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            No spam, unsubscribe at any time.
        </p>
    </div>

    <!-- Archive Widget -->
    <div class="blog-sidebar">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Archive</h3>
        <ul class="space-y-2">
            @php
                // Get recent months for archive (you could pass this from controller)
                $archiveMonths = collect();
                for($i = 0; $i < 6; $i++) {
                    $date = now()->subMonths($i);
                    $archiveMonths->push([
                        'year' => $date->year,
                        'month' => $date->month,
                        'name' => $date->format('F Y'),
                        'count' => 0 // This would be populated from actual data
                    ]);
                }
            @endphp
            @foreach($archiveMonths as $archive)
            <li>
                <a href="{{ route('blog.archive', ['year' => $archive['year'], 'month' => $archive['month']]) }}" 
                   class="flex items-center justify-between text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors py-1">
                    <span>{{ $archive['name'] }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-500">
                        ({{ $archive['count'] }})
                    </span>
                </a>
            </li>
            @endforeach
        </ul>
        <div class="mt-3">
            <a href="{{ route('blog.archive') }}" 
               class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 transition-colors">
                View All Archives →
            </a>
        </div>
    </div>

    <!-- Social Links Widget -->
    <div class="blog-sidebar">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Follow Us</h3>
        <div class="flex space-x-3">
            <a href="#" class="p-2 text-gray-500 hover:text-blue-500 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
            </a>
            <a href="#" class="p-2 text-gray-500 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>
            <a href="#" class="p-2 text-gray-500 hover:text-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
            </a>
            <a href="#" class="p-2 text-gray-500 hover:text-red-600 transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
            </a>
        </div>
    </div>
</aside>