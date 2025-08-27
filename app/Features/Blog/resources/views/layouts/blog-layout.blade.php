<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>{{ $metaTitle ?? ($title ?? 'Blog') }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover the latest insights and stories from ' . config('app.name') }}">
    <meta name="keywords" content="{{ $metaKeywords ?? '' }}">
    
    <!-- Open Graph / Facebook -->
    @if(isset($openGraphMeta))
        @foreach($openGraphMeta as $property => $content)
            <meta property="{{ $property }}" content="{{ $content }}">
        @endforeach
    @else
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ $metaTitle ?? ($title ?? 'Blog') }}">
        <meta property="og:description" content="{{ $metaDescription ?? 'Discover the latest insights and stories from ' . config('app.name') }}">
        <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    @endif

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $metaTitle ?? ($title ?? 'Blog') }}">
    <meta property="twitter:description" content="{{ $metaDescription ?? 'Discover the latest insights and stories from ' . config('app.name') }}">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- JSON-LD Schema Markup -->
    @if(isset($structuredData))
    <script type="application/ld+json">
    {!! json_encode($structuredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    @if(isset($breadcrumbSchema))
    <script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
	
	<!-- Lucide Icons -->
	<script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Core Thorium90 Styles -->
    @include('public.partials.thorium90-styles')
    
    <!-- Blog Specific Styles -->
    <link rel="stylesheet" href="{{ asset('css/features/blog/blog.css') }}?v={{ time() }}">
    
    <!-- Performance & Accessibility Enhancements -->
    <link rel="preload" href="{{ asset('css/features/blog/blog.css') }}" as="style">
    
    @stack('styles')
</head>

<body class="home-template hero-pattern blog-template" data-theme="{{ $theme ?? 'default' }}">
    <!-- Skip Navigation Link for Accessibility -->
    <a href="#main-content" class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 transition-all">Skip to main content</a>
    
    <!-- Header (inherit from core) -->
    @include('public.partials.thorium90-header')

    <!-- Breadcrumbs -->
    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav class="blog-breadcrumbs bg-gray-50 dark:bg-gray-900 py-4 border-b border-gray-200 dark:border-gray-700" role="navigation" aria-label="Breadcrumb navigation">
        <div class="blog-container">
            <ol class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400" itemscope itemtype="https://schema.org/BreadcrumbList">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="flex items-center" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <meta itemprop="position" content="{{ $loop->iteration }}" />
                        @if(!$loop->first)
                            <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        
                        @if($breadcrumb['url'])
                            <a href="{{ $breadcrumb['url'] }}" class="hover:text-gray-900 dark:hover:text-gray-100 transition-colors" itemprop="item">
                                <span itemprop="name">{{ $breadcrumb['title'] }}</span>
                            </a>
                        @else
                            <span class="text-gray-900 dark:text-gray-100 font-medium" itemprop="name">
                                {{ $breadcrumb['title'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>
    @endif

    <!-- Main Content Area -->
    <main id="main-content" class="py-8 md:py-12" role="main" tabindex="-1">
        @yield('content')
    </main>

    <!-- Footer (inherit from core) -->
    @include('public.partials.thorium90-footer')

    <!-- Custom Page Scripts -->
    @stack('scripts')

	<!-- Lucide Icon -->
	<script>
      lucide.createIcons();
    </script>
	
    <!-- Core Thorium90 Scripts -->
    @include('public.partials.thorium90-scripts')

    <!-- Blog specific JavaScript - Optimized for Performance & Accessibility -->
    <script>
        // Blog Management Class - Modern ES6+ Implementation
        class BlogManager {
            constructor() {
                this.elements = this.cacheElements();
                this.initEventListeners();
                this.initIntersectionObserver();
                this.initAccessibilityFeatures();
            }

            cacheElements() {
                return {
                    searchInput: document.querySelector('.blog-search input'),
                    blogCards: document.querySelectorAll('.blog-card'),
                    shareButtons: document.querySelectorAll('[data-share]'),
                    copyButtons: document.querySelectorAll('[data-copy]')
                };
            }

            initEventListeners() {
                // Use event delegation for better performance
                document.addEventListener('click', this.handleClick.bind(this));
                document.addEventListener('keydown', this.handleKeydown.bind(this));
                
                // Debounced search
                if (this.elements.searchInput) {
                    this.elements.searchInput.addEventListener('input', 
                        this.debounce(this.handleSearch.bind(this), 300)
                    );
                    this.elements.searchInput.addEventListener('keypress', this.handleSearchKeypress.bind(this));
                }
            }

            initIntersectionObserver() {
                // Only initialize if user hasn't requested reduced motion
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('blog-fade-in');
                            observer.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                // Observe blog cards with performance optimization
                this.elements.blogCards.forEach(card => {
                    observer.observe(card);
                });
            }

            initAccessibilityFeatures() {
                // Add keyboard navigation to blog cards
                this.elements.blogCards.forEach(card => {
                    if (!card.hasAttribute('tabindex')) {
                        card.setAttribute('tabindex', '0');
                    }
                    card.setAttribute('role', 'article');
                });

                // Announce search results to screen readers
                if (this.elements.searchInput) {
                    const resultsContainer = document.querySelector('.blog-search-results');
                    if (resultsContainer) {
                        resultsContainer.setAttribute('aria-live', 'polite');
                        resultsContainer.setAttribute('aria-label', 'Search results');
                    }
                }
            }

            handleClick(event) {
                const shareButton = event.target.closest('[data-share]');
                const copyButton = event.target.closest('[data-copy]');

                if (shareButton) {
                    event.preventDefault();
                    this.shareBlogPost(
                        shareButton.dataset.url,
                        shareButton.dataset.title,
                        shareButton.dataset.share
                    );
                }

                if (copyButton) {
                    event.preventDefault();
                    this.copyBlogLink(copyButton.dataset.copy);
                }

                // Handle blog card clicks
                const blogCard = event.target.closest('.blog-card');
                if (blogCard && !event.target.closest('button, a')) {
                    this.handleCardClick(blogCard);
                }
            }

            handleKeydown(event) {
                // Handle keyboard navigation for blog cards
                if (event.target.classList.contains('blog-card')) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        this.handleCardClick(event.target);
                    }
                }
            }

            handleCardClick(card) {
                const link = card.querySelector('a[href]');
                if (link) {
                    if (event.ctrlKey || event.metaKey) {
                        window.open(link.href, '_blank');
                    } else {
                        window.location.href = link.href;
                    }
                }
            }

            handleSearch(event) {
                const query = event.target.value.trim();
                if (query.length > 2) {
                    // Implement live search if needed
                    console.log('Searching for:', query);
                }
            }

            handleSearchKeypress(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const query = event.target.value.trim();
                    if (query) {
                        window.location.href = `{{ route('blog.index') }}?search=${encodeURIComponent(query)}`;
                    }
                }
            }

            shareBlogPost(url, title, platform) {
                const shareUrls = {
                    twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`,
                    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
                    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`
                };

                if (shareUrls[platform]) {
                    // Check if user can open popups
                    const popup = window.open(shareUrls[platform], '_blank', 'width=600,height=400,noopener,noreferrer');
                    
                    if (!popup) {
                        // Fallback: navigate to share URL in same tab
                        window.location.href = shareUrls[platform];
                    }
                    
                    // Analytics tracking could be added here
                    this.trackEvent('social_share', { platform, url, title });
                }
            }

            async copyBlogLink(url) {
                try {
                    await navigator.clipboard.writeText(url);
                    this.showNotification('Link copied to clipboard!', 'success');
                } catch (err) {
                    // Fallback for older browsers
                    this.fallbackCopy(url);
                }
                
                this.trackEvent('link_copy', { url });
            }

            fallbackCopy(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    this.showNotification('Link copied to clipboard!', 'success');
                } catch (err) {
                    this.showNotification('Unable to copy link', 'error');
                } finally {
                    document.body.removeChild(textArea);
                }
            }

            showNotification(message, type = 'info') {
                // Create accessible toast notification
                const notification = document.createElement('div');
                notification.className = `blog-notification blog-notification-${type}`;
                notification.setAttribute('role', 'alert');
                notification.setAttribute('aria-live', 'assertive');
                notification.textContent = message;
                
                // Style the notification
                Object.assign(notification.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    padding: '12px 20px',
                    borderRadius: '8px',
                    backgroundColor: type === 'error' ? '#dc2626' : '#059669',
                    color: 'white',
                    fontWeight: '500',
                    zIndex: '9999',
                    transform: 'translateX(100%)',
                    transition: 'transform 0.3s ease'
                });

                document.body.appendChild(notification);
                
                // Animate in
                requestAnimationFrame(() => {
                    notification.style.transform = 'translateX(0)';
                });

                // Remove after 3 seconds
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }

            trackEvent(event, data) {
                // Analytics tracking integration point
                if (typeof gtag !== 'undefined') {
                    gtag('event', event, data);
                }
                console.log('Blog event tracked:', event, data);
            }

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        }

        // Initialize Blog Manager when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            // Only initialize if we're on a blog page
            if (document.querySelector('.blog-template')) {
                window.blogManager = new BlogManager();
            }
        });

        // Legacy function support for existing templates
        function shareBlogPost(url, title, platform) {
            if (window.blogManager) {
                window.blogManager.shareBlogPost(url, title, platform);
            }
        }

        function copyBlogLink(url) {
            if (window.blogManager) {
                window.blogManager.copyBlogLink(url);
            }
        }
    </script>

</body>
</html>