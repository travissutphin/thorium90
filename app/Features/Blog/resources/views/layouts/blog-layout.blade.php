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

    <!-- Tailwind CSS - Use Vite built assets -->
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
	
	<!-- Lucide Icons -->
	<script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Core Thorium90 Styles -->
    @include('public.partials.thorium90-styles')
    
    <!-- Blog Specific Styles -->
    <link rel="stylesheet" href="{{ asset('css/features/blog/blog.css') }}?v={{ time() }}">
    
    <!-- Performance & Accessibility Enhancements -->
    <link rel="preload" href="{{ asset('css/features/blog/blog.css') }}" as="style">
    
    @stack('styles')
    
    <!-- Table of Contents Styling -->
    <style>
        /* Table of Contents Styling */
        .toc-nav {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        
        .toc-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .toc-sublist {
            list-style: none;
            padding-left: 1rem;
            margin: 0.25rem 0;
            border-left: 2px solid #e5e7eb;
        }
        
        .dark .toc-sublist {
            border-left-color: #374151;
        }
        
        .toc-item {
            margin: 0.25rem 0;
        }
        
        .toc-link {
            display: block;
            padding: 0.25rem 0;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 0.25rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            line-height: 1.4;
        }
        
        .toc-link:hover {
            color: #374151;
            background-color: #f3f4f6;
        }
        
        .toc-link.toc-active {
            color: #2563eb;
            background-color: #dbeafe;
            font-weight: 500;
        }
        
        .dark .toc-link {
            color: #9ca3af;
        }
        
        .dark .toc-link:hover {
            color: #d1d5db;
            background-color: #374151;
        }
        
        .dark .toc-link.toc-active {
            color: #60a5fa;
            background-color: #1e3a8a;
        }
        
        /* Level-specific indentation and styling */
        .toc-level-2 .toc-link {
            font-weight: 500;
        }
        
        .toc-level-3 .toc-link {
            padding-left: 1rem;
            font-size: 0.8125rem;
        }
        
        .toc-level-4 .toc-link {
            padding-left: 1.5rem;
            font-size: 0.8125rem;
            color: #9ca3af;
        }
        
        .toc-level-5 .toc-link,
        .toc-level-6 .toc-link {
            padding-left: 2rem;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .dark .toc-level-4 .toc-link,
        .dark .toc-level-5 .toc-link,
        .dark .toc-level-6 .toc-link {
            color: #6b7280;
        }
        
        /* Smooth scroll behavior for the page */
        html {
            scroll-behavior: smooth;
        }
        
        /* Heading anchor styling */
        .blog-prose h2,
        .blog-prose h3,
        .blog-prose h4,
        .blog-prose h5,
        .blog-prose h6 {
            scroll-margin-top: 5rem;
        }
        
        /* Optional: Add hover effect to headings to show they're linkable */
        .blog-prose h2:hover,
        .blog-prose h3:hover,
        .blog-prose h4:hover,
        .blog-prose h5:hover,
        .blog-prose h6:hover {
            position: relative;
        }
        
        .blog-prose h2:hover::before,
        .blog-prose h3:hover::before,
        .blog-prose h4:hover::before,
        .blog-prose h5:hover::before,
        .blog-prose h6:hover::before {
            content: '#';
            position: absolute;
            left: -1.5rem;
            color: #9ca3af;
            font-weight: normal;
            opacity: 0.7;
        }
    </style>
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
                this.generateTableOfContents();
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

            generateTableOfContents() {
                const tocContainer = document.getElementById('table-of-contents');
                const contentArea = document.querySelector('.blog-prose');
                
                if (!tocContainer || !contentArea) return;

                // Find all headings in the post content (H2-H6, skip H1 as it's the post title)
                const headings = contentArea.querySelectorAll('h2, h3, h4, h5, h6');
                
                if (headings.length === 0) {
                    tocContainer.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-xs italic">No headings found in this post.</p>';
                    return;
                }

                // Generate unique IDs for headings if they don't have them
                headings.forEach((heading, index) => {
                    if (!heading.id) {
                        const text = heading.textContent.trim();
                        const id = text.toLowerCase()
                            .replace(/[^\w\s-]/g, '') // Remove special characters
                            .replace(/[\s_]+/g, '-') // Replace spaces and underscores with hyphens
                            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
                        heading.id = id || `heading-${index + 1}`;
                    }
                });

                // Build the TOC HTML
                let tocHTML = '<nav class="toc-nav" role="navigation" aria-label="Table of contents"><ul class="toc-list">';
                let currentLevel = 2;
                
                headings.forEach((heading, index) => {
                    const level = parseInt(heading.tagName.charAt(1));
                    const text = heading.textContent.trim();
                    const id = heading.id;
                    
                    // Handle nesting levels
                    if (level > currentLevel) {
                        // Open nested lists
                        for (let i = currentLevel; i < level; i++) {
                            tocHTML += '<li><ul class="toc-sublist">';
                        }
                    } else if (level < currentLevel) {
                        // Close nested lists
                        for (let i = currentLevel; i > level; i--) {
                            tocHTML += '</ul></li>';
                        }
                    }
                    
                    tocHTML += `
                        <li class="toc-item toc-level-${level}">
                            <a href="#${id}" class="toc-link" data-target="${id}">
                                ${text}
                            </a>
                        </li>
                    `;
                    
                    currentLevel = level;
                });
                
                // Close any remaining nested lists
                while (currentLevel > 2) {
                    tocHTML += '</ul></li>';
                    currentLevel--;
                }
                
                tocHTML += '</ul></nav>';
                tocContainer.innerHTML = tocHTML;

                // Add smooth scrolling behavior
                this.initTocScrolling();
                
                // Add active section highlighting
                this.initTocHighlighting();
            }

            initTocScrolling() {
                const tocLinks = document.querySelectorAll('.toc-link');
                
                tocLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = e.target.getAttribute('data-target');
                        const targetElement = document.getElementById(targetId);
                        
                        if (targetElement) {
                            const offset = 80; // Account for any fixed header
                            const elementPosition = targetElement.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - offset;

                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                            
                            // Update URL hash
                            history.pushState(null, null, `#${targetId}`);
                            
                            // Track analytics
                            this.trackEvent('toc_navigation', { target: targetId });
                        }
                    });
                });
            }

            initTocHighlighting() {
                const tocLinks = document.querySelectorAll('.toc-link');
                const headings = document.querySelectorAll('.blog-prose h2, .blog-prose h3, .blog-prose h4, .blog-prose h5, .blog-prose h6');
                
                if (headings.length === 0) return;

                // Create intersection observer for heading visibility
                const observerOptions = {
                    rootMargin: '-80px 0px -80% 0px',
                    threshold: 0
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            // Remove active class from all TOC links
                            tocLinks.forEach(link => link.classList.remove('toc-active'));
                            
                            // Add active class to current section's TOC link
                            const activeLink = document.querySelector(`[data-target="${entry.target.id}"]`);
                            if (activeLink) {
                                activeLink.classList.add('toc-active');
                            }
                        }
                    });
                }, observerOptions);

                // Observe all headings
                headings.forEach(heading => observer.observe(heading));
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