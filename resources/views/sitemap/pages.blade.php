<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    
    <!-- Homepage -->
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Pages -->
    @foreach($pages as $page)
    <url>
        <loc>{{ url('/' . $page->slug) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
        <changefreq>{{ $page->is_featured ? 'daily' : 'weekly' }}</changefreq>
        <priority>{{ $page->is_featured ? '0.9' : '0.8' }}</priority>
        @if($page->schema_type === 'NewsArticle')
        <news:news>
            <news:publication>
                <news:name>{{ config('app.name') }}</news:name>
                <news:language>{{ str_replace('_', '-', app()->getLocale()) }}</news:language>
            </news:publication>
            <news:publication_date>{{ $page->created_at->toAtomString() }}</news:publication_date>
            <news:title>{{ $page->title }}</news:title>
        </news:news>
        @endif
    </url>
    @endforeach

    <!-- Blog Content -->
    @if(isset($blogSitemapData) && count($blogSitemapData) > 0)
        @foreach($blogSitemapData as $blogItem)
        <url>
            <loc>{{ $blogItem['url'] }}</loc>
            @if(isset($blogItem['lastmod']))
            <lastmod>{{ $blogItem['lastmod']->toAtomString() }}</lastmod>
            @endif
            <changefreq>{{ $blogItem['changefreq'] ?? 'weekly' }}</changefreq>
            <priority>{{ $blogItem['priority'] ?? '0.8' }}</priority>
        </url>
        @endforeach
    @endif
</urlset>
