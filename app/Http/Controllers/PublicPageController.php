<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    /**
     * Display the specified page using the template system.
     */
    public function show(Page $page)
    {
        // Only show published pages to the public
        if ($page->status !== 'published' || !$page->published_at || $page->published_at > now()) {
            abort(404);
        }

        $page->load('user');

        // Prepare page data for the template system
        $pageData = [
            'id' => $page->id,
            'type' => 'page',
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'template' => $page->template ?: 'core-page',
            'layout' => $page->layout,
            'theme' => $page->theme,
            'blocks' => $page->blocks ?: [],
            'template_config' => $page->template_config ?: [],
            'meta' => [
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description ?: $page->excerpt,
                'keywords' => $page->meta_keywords,
            ],
            'user' => $page->user ? [
                'id' => $page->user->id,
                'name' => $page->user->name,
            ] : null,
            'published_at' => $page->published_at?->toISOString(),
            'updated_at' => $page->updated_at->toISOString(),
            'created_at' => $page->created_at->toISOString(),
        ];

        // Generate schema data
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => $page->schema_type ?: 'WebPage',
            'name' => $page->title,
            'description' => $page->meta_description ?: $page->excerpt,
            'url' => route('pages.show', $page->slug),
            'datePublished' => $page->published_at?->toISOString(),
            'dateModified' => $page->updated_at->toISOString(),
            'author' => [
                '@type' => 'Person',
                'name' => $page->user?->name,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => config('app.url'),
            ],
        ];

        if ($page->schema_type === 'Article') {
            $schemaData['@type'] = 'Article';
            $schemaData['headline'] = $page->title;
            $schemaData['articleBody'] = strip_tags($page->content);
            $schemaData['wordCount'] = str_word_count(strip_tags($page->content));
        }

        return Inertia::render('public/page', [
            'page' => $pageData,
            'schemaData' => $schemaData,
            'seoData' => [
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description ?: $page->excerpt,
                'keywords' => $page->meta_keywords,
                'canonical' => route('pages.show', $page->slug),
                'ogType' => 'article',
                'ogTitle' => $page->meta_title ?: $page->title,
                'ogDescription' => $page->meta_description ?: $page->excerpt,
                'ogUrl' => route('pages.show', $page->slug),
                'twitterCard' => 'summary_large_image',
                'twitterTitle' => $page->meta_title ?: $page->title,
                'twitterDescription' => $page->meta_description ?: $page->excerpt,
            ],
        ]);
    }
}
