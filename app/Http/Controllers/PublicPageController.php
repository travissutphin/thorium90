<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function index()
    {
        // Find the home page or create default data
        $page = Page::where('slug', 'home')
                    ->where('status', 'published')
                    ->first();
        
        // Fallback to default content if no home page exists
        if (!$page) {
            $page = (object) [
                'title' => 'Content Management Redefined',
                'excerpt' => 'Experience the power of AI-driven content management with human verification. Build, manage, and scale your digital presence with confidence.',
                'meta_title' => 'Thorium90 - Content Management Redefined',
                'meta_description' => 'Experience the power of AI-driven content management with human verification.',
                'meta_keywords' => 'content management, AI, CMS, digital content, Thorium90',
                'schema_type' => 'WebPage',
                'template' => 'home',
                'slug' => 'home',
                'status' => 'published',
                'published_at' => now(),
                'content' => '',
                'layout' => 'thorium90',
                'theme' => 'default',
                'template_config' => [
                    'sections' => [
                        'hero',
                        'features', 
                        'tech-stack',
                        'packages',
                        'showcase',
                        'stats',
                        'cta'
                    ]
                ],
            ];
        } else {
            $page->load('user');
        }

        // Use the unified template system
        return view('public.layouts.thorium90-template', compact('page'));
    }
	
	
    /**
     * Display the specified page using the template system.
     */
    public function show(Page $page)
    {
        // Only show published pages to the public
        if ($page->status !== 'published' || !$page->published_at || $page->published_at > now()) {
            abort(404);
        }

        // Load user relationship for SEO and structured data
        $page->load('user');

        // Use the Thorium90 template based on React design
        return view('public.layouts.thorium90-template', compact('page'));
    }
}
