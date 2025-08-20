<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>{{ $page->meta_title ?? $page->title }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $page->meta_description ?? $page->excerpt ?? 'Welcome to ' . config('app.name') }}">
    <meta name="keywords" content="{{ $page->meta_keywords ?? '' }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="og:description" content="{{ $page->meta_description ?? $page->excerpt ?? 'Welcome to ' . config('app.name') }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="twitter:description" content="{{ $page->meta_description ?? $page->excerpt ?? 'Welcome to ' . config('app.name') }}">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- JSON-LD Schema Markup -->
    @php
        $schemaData = $page->schema_data;
        // Fallback schema if none generated
        if (!$schemaData && $page->schema_type) {
            $schemaData = [
                '@context' => 'https://schema.org',
                '@type' => $page->schema_type,
                'name' => $page->title,
                'headline' => $page->title,
                'description' => $page->meta_description ?? $page->excerpt,
            ];
        }
    @endphp
    @if($schemaData)
    <script type="application/ld+json">
    {!! json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
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
    
    <!-- Thorium90 Custom Styles -->
    @include('public.partials.thorium90-styles')
</head>

<body class="home-template hero-pattern {{ $page->template_config['custom_class'] ?? '' }}" data-theme="{{ $page->theme ?? 'default' }}">
    
    <!-- Header -->
    @include('public.partials.thorium90-header')

    <!-- Main Content Area -->
    <main>
        
		<!-- Dynamic Page Content Placeholder 
		*** THIS WILL PULL DATA IN THE CONTACT COLUMN OF DB TABLE PAGES *** -->
        {{-- @if($page->content && trim(strip_tags($page->content)))
            <section class="py-16 md:py-24">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto">
                        <header class="text-center mb-12">
                            <h1 class="text-4xl md:text-5xl font-bold mb-6 text-gray-900">
                                {{ $page->title }}
                            </h1>
                            @if($page->meta_description)
                                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                                    {{ $page->meta_description }}
                                </p>
                            @endif
                        </header>
                        
                        <div class="prose prose-lg max-w-none">
                            {!! $page->content !!}
                        </div>
                    </div>
                </div>
            </section>
        @endif --}}

        <!-- Template Sections -->
        @switch($page->slug ?? $page['slug'] ?? '')
            @case('')
            @case('home')
                @include('public.pages.home-page')
                @break
			
			@case('about')
				@include('public.pages.about-page')
                @break			

			@case('team')
				@include('public.pages.team-page')
				@break
				
            @default
                <!-- Article Page Template -->
                <article itemscope itemtype="https://schema.org/Article" class="py-16 md:py-24">
                    <div class="container mx-auto px-4">
                        <div class="max-w-4xl mx-auto">
                            <header class="text-center mb-12">
                                <h1 itemprop="headline" class="text-4xl md:text-5xl font-bold mb-6 text-gray-900">
                                    {{ $page->title }}
                                </h1>
                                @if($page->excerpt || $page->meta_description)
                                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                                        {{ $page->excerpt ?? $page->meta_description }}
                                    </p>
                                @endif
                                <div class="mt-6 flex items-center justify-center space-x-4 text-sm text-gray-500">
                                    @if($page->user)
                                        <span itemprop="author" itemscope itemtype="https://schema.org/Person">
                                            By <span itemprop="name">{{ $page->user->name }}</span>
                                        </span>
                                    @endif
                                    @if($page->published_at)
                                        <span>•</span>
                                        <time itemprop="datePublished" datetime="{{ $page->published_at->toIso8601String() }}">
                                            {{ $page->published_at->format('F j, Y') }}
                                        </time>
                                    @endif
                                    @if($page->reading_time)
                                        <span>•</span>
                                        <span>{{ $page->reading_time }} min read</span>
                                    @endif
                                </div>
                                
                                @if($page->topics && is_array($page->topics) && count($page->topics) > 0)
                                    <div class="mt-6 flex items-center justify-center flex-wrap gap-2">
                                        <span class="text-sm font-medium text-gray-500">Topics:</span>
                                        @foreach($page->topics as $topic)
                                            <span class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-full">
                                                {{ $topic }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </header>
                            
                            <section itemprop="articleBody" class="prose prose-lg max-w-none">
                                {!! $page->content !!}
                            </section>
                        </div>
                    </div>
                </article>

        @endswitch 
        
    </main>

    <!-- Footer -->
    @include('public.partials.thorium90-footer')

    <!-- Custom Page Scripts -->
    @if($page->template_config['page_scripts'] ?? false)
        <script>
            {!! $page->template_config['page_scripts'] !!}
        </script>
    @endif

	<!-- Lucide Icon -->
	<script>
      lucide.createIcons();
    </script>
	
    <!-- Thorium90 Core Scripts -->
    @include('public.partials.thorium90-scripts')

</body>
</html>