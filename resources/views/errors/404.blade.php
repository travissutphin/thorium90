@php
    // Create a page object for the 404 template system to match thorium90-template expectations
    $page = (object) [
        'title' => 'Page Not Found',
        'excerpt' => 'Sorry, the page you are looking for could not be found.',
        'meta_title' => '404 - Page Not Found',
        'meta_description' => 'The page you are looking for could not be found.',
        'meta_keywords' => '404, page not found, error',
        'schema_type' => 'WebPage',
        'slug' => '404-error',
        'status' => 'published',
        'published_at' => now(),
        'theme' => 'default',
        'template_config' => [
            'custom_class' => 'error-404-page',
            'page_scripts' => null
        ],
        'content' => '',
        'user' => null,
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>{{ $page->meta_title }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $page->meta_description }}">
    <meta name="keywords" content="{{ $page->meta_keywords }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->meta_title }}">
    <meta property="og:description" content="{{ $page->meta_description }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $page->meta_title }}">
    <meta property="twitter:description" content="{{ $page->meta_description }}">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons -->
	<script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Thorium90 Custom Styles -->
    @include('public.partials.thorium90-styles')
</head>

<body class="home-template hero-pattern {{ $page->template_config['custom_class'] ?? '' }}" data-theme="{{ $page->theme ?? 'default' }}">
    
    <!-- Header -->
    @include('public.partials.thorium90-header')

    <!-- 404 Content -->
    <main class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 pt-16">
        <div class="container mx-auto px-4">
            @include('public.pages.404-fallback')
        </div>
    </main>

    <!-- Footer -->
    @include('public.partials.thorium90-footer')

    <!-- Scripts -->
    @include('public.partials.thorium90-scripts')

    @if($page->template_config['page_scripts'] ?? false)
        <script>
            {!! $page->template_config['page_scripts'] !!}
        </script>
    @endif

</body>
</html>
