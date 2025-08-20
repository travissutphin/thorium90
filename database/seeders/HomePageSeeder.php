<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if home page already exists
        $existingHome = Page::where('slug', 'home')->first();
        
        if (!$existingHome) {
            Page::create([
                'title' => 'Content Management Redefined',
                'slug' => 'home',
                'content' => '', // Empty since we use sections for home page
                'excerpt' => 'Experience the power of AI-driven content management with human verification. Build, manage, and scale your digital presence with confidence.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'Thorium90 - Content Management Redefined',
                'meta_description' => 'Experience the power of AI-driven content management with human verification. Build, manage, and scale your digital presence with confidence.',
                'meta_keywords' => 'content management, AI, CMS, digital content, Thorium90',
                'schema_type' => 'WebPage',
                'template' => 'home',
                'layout' => 'thorium90',
                'theme' => 'default',
                'blocks' => [],
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
                'faq_data' => [],
                'content_type' => 'homepage',
                'topics' => ['Thorium90', 'CMS'],
                'keywords' => ['thorium90', 'cms', 'content management'],
                'published_at' => now(),
                'user_id' => 1, // Assumes admin user exists
            ]);
            
            $this->command->info('Home page created successfully.');
        } else {
            $this->command->info('Home page already exists, skipping creation.');
        }
    }
}