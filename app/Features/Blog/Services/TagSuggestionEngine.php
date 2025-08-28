<?php

namespace App\Features\Blog\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Features\Blog\Models\BlogTag;

class TagSuggestionEngine
{
    /**
     * Suggest tags based on title, content, and extracted keywords.
     */
    public function suggestTags(string $title, string $content, Collection $keywords): Collection
    {
        $suggestions = collect();

        // Get existing tags for matching
        $existingTags = BlogTag::pluck('name', 'id');
        
        // Strategy 1: Direct keyword matching with existing tags
        $keywordMatches = $this->matchKeywordsToExistingTags($keywords, $existingTags);
        $suggestions = $suggestions->merge($keywordMatches);

        // Strategy 2: Technology and framework detection
        $techTags = $this->suggestTechnologyTags($title, $content);
        $suggestions = $suggestions->merge($techTags);

        // Strategy 3: Content type classification
        $contentTypeTags = $this->suggestContentTypeTags($title, $content);
        $suggestions = $suggestions->merge($contentTypeTags);

        // Strategy 4: Skill level detection
        $skillLevelTags = $this->suggestSkillLevelTags($title, $content);
        $suggestions = $suggestions->merge($skillLevelTags);

        // Strategy 5: Popular tag suggestions based on similarity
        $popularTags = $this->suggestPopularSimilarTags($keywords, $existingTags);
        $suggestions = $suggestions->merge($popularTags);

        return $suggestions
            ->unique()
            ->map(function ($tag) {
                return [
                    'name' => $tag,
                    'confidence' => $this->calculateTagConfidence($tag),
                    'reason' => $this->getTagSuggestionReason($tag),
                    'exists' => BlogTag::where('name', $tag)->exists(),
                ];
            })
            ->values() // Ensure array indices are sequential (0, 1, 2...) not object keys
            ->sortByDesc('confidence')
            ->take(8);
    }

    /**
     * Match keywords to existing blog tags.
     */
    protected function matchKeywordsToExistingTags(Collection $keywords, Collection $existingTags): Collection
    {
        $matches = collect();

        foreach ($keywords as $keyword) {
            foreach ($existingTags as $tagId => $tagName) {
                // Exact match
                if (strcasecmp($keyword, $tagName) === 0) {
                    $matches->push($tagName);
                    continue;
                }

                // Partial match (keyword contains tag or vice versa)
                if (stripos($keyword, $tagName) !== false || stripos($tagName, $keyword) !== false) {
                    if (strlen($tagName) > 2) { // Avoid very short matches
                        $matches->push($tagName);
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Suggest technology-related tags based on content analysis.
     */
    protected function suggestTechnologyTags(string $title, string $content): Collection
    {
        $techTags = collect();
        $combinedText = strtolower($title . ' ' . $content);

        // Technology mappings with confidence patterns
        $techMappings = [
            // Technical Tags
            'Laravel' => ['laravel', 'artisan', 'eloquent', 'blade', 'composer'],
            'PHP' => ['php', '<?php', 'namespace', 'class', 'function'],
            'JavaScript' => ['javascript', 'js', 'jquery', 'dom', 'ajax'],
            'Vue.js' => ['vue', 'vuejs', 'vue.js', 'component', 'directive'],
            'React' => ['react', 'jsx', 'component', 'props', 'state'],
            'API' => ['api', 'endpoint', 'rest', 'json', 'http'],
            'Database' => ['database', 'mysql', 'sql', 'query', 'migration'],
            'Authentication' => ['auth', 'login', 'password', 'token', 'session'],
            'Testing' => ['test', 'testing', 'phpunit', 'assertion', 'mock'],
            'Performance' => ['performance', 'optimization', 'cache', 'speed', 'memory'],
            'Security' => ['security', 'csrf', 'xss', 'encryption', 'hash'],
            'Docker' => ['docker', 'container', 'dockerfile', 'compose'],
            'Git' => ['git', 'github', 'commit', 'branch', 'merge'],
            
            // Business & Soft Skills Tags
            'Coaching' => ['coaching', 'coach', 'mentor', 'mentoring', 'guidance'],
            'Leadership' => ['leadership', 'leader', 'leading', 'management', 'manager'],
            'Communication' => ['communication', 'conversation', 'dialogue', 'questions', 'listening'],
            'Professional Development' => ['professional development', 'career growth', 'skills development', 'self-improvement'],
            'Business' => ['business', 'strategy', 'entrepreneurship', 'corporate', 'workplace'],
            'Productivity' => ['productivity', 'efficiency', 'time management', 'habits', 'workflow'],
            'Team Building' => ['team building', 'teamwork', 'collaboration', 'relationships'],
            'Psychology' => ['psychology', 'behavior', 'mindset', 'thinking', 'mental'],
            'Self-Improvement' => ['self-improvement', 'personal growth', 'development', 'improvement'],
            'Book Summary' => ['summary', 'book review', 'key insights', 'takeaways'],
            'Education' => ['education', 'learning', 'teaching', 'training', 'instruction'],
            'Sales' => ['sales', 'selling', 'negotiation', 'customer', 'client'],
        ];

        foreach ($techMappings as $tag => $patterns) {
            $confidence = 0;
            foreach ($patterns as $pattern) {
                if (stripos($combinedText, $pattern) !== false) {
                    $confidence++;
                }
            }
            
            if ($confidence >= 1) {
                $techTags->push($tag);
            }
        }

        return $techTags;
    }

    /**
     * Suggest content type tags based on title and content structure.
     */
    protected function suggestContentTypeTags(string $title, string $content): Collection
    {
        $contentTypeTags = collect();
        $titleLower = strtolower($title);
        $contentLower = strtolower($content);

        // Content type patterns
        $typePatterns = [
            'Tutorial' => [
                'title' => ['how to', 'tutorial', 'guide', 'step by step', 'learn'],
                'content' => ['step 1', 'first,', 'next,', 'finally,', 'installation']
            ],
            'Review' => [
                'title' => ['review', 'comparison', 'vs', 'versus', 'compare'],
                'content' => ['pros', 'cons', 'advantages', 'disadvantages', 'rating']
            ],
            'News' => [
                'title' => ['news', 'announced', 'released', 'update', '2024', '2025'],
                'content' => ['recently', 'announced', 'new version', 'update']
            ],
            'Beginner Guide' => [
                'title' => ['beginner', 'introduction', 'getting started', 'basics'],
                'content' => ['beginner', 'basic', 'simple', 'easy', 'introduction']
            ],
            'Advanced' => [
                'title' => ['advanced', 'expert', 'deep dive', 'mastering'],
                'content' => ['complex', 'advanced', 'sophisticated', 'expert']
            ],
            'Best Practices' => [
                'title' => ['best practices', 'tips', 'recommendations', 'should'],
                'content' => ['best practice', 'recommend', 'should', 'avoid', 'tip']
            ],
        ];

        foreach ($typePatterns as $tag => $patterns) {
            $titleMatch = false;
            $contentMatch = false;

            foreach ($patterns['title'] as $pattern) {
                if (stripos($titleLower, $pattern) !== false) {
                    $titleMatch = true;
                    break;
                }
            }

            foreach ($patterns['content'] as $pattern) {
                if (stripos($contentLower, $pattern) !== false) {
                    $contentMatch = true;
                    break;
                }
            }

            if ($titleMatch || $contentMatch) {
                $contentTypeTags->push($tag);
            }
        }

        return $contentTypeTags;
    }

    /**
     * Suggest skill level tags based on content complexity.
     */
    protected function suggestSkillLevelTags(string $title, string $content): Collection
    {
        $skillTags = collect();
        $combinedText = strtolower($title . ' ' . $content);

        // Beginner indicators
        if (preg_match('/\b(beginner|basic|introduction|getting started|simple|easy|first time)\b/i', $combinedText)) {
            $skillTags->push('Beginner');
        }

        // Intermediate indicators
        if (preg_match('/\b(intermediate|moderate|practical|implementation|building)\b/i', $combinedText)) {
            $skillTags->push('Intermediate');
        }

        // Advanced indicators
        if (preg_match('/\b(advanced|expert|complex|optimization|architecture|deep dive|mastering)\b/i', $combinedText)) {
            $skillTags->push('Advanced');
        }

        return $skillTags;
    }

    /**
     * Suggest popular tags that are similar to extracted keywords.
     */
    protected function suggestPopularSimilarTags(Collection $keywords, Collection $existingTags): Collection
    {
        $suggestions = collect();

        // Get tag usage statistics
        $popularTags = BlogTag::withCount('blogPosts')
            ->orderByDesc('blog_posts_count')
            ->limit(20)
            ->pluck('name');

        // Find semantic similarities
        foreach ($keywords as $keyword) {
            foreach ($popularTags as $tag) {
                $similarity = $this->calculateStringSimilarity($keyword, $tag);
                if ($similarity > 0.6) { // 60% similarity threshold
                    $suggestions->push($tag);
                }
            }
        }

        return $suggestions;
    }

    /**
     * Calculate confidence score for a tag suggestion.
     */
    protected function calculateTagConfidence(string $tag): int
    {
        $confidence = 50; // Base confidence

        // Boost if tag already exists
        if (BlogTag::where('name', $tag)->exists()) {
            $confidence += 20;
        }

        // Boost for technology tags
        $techTerms = config('blog.ai.tech_terms', []);
        if (in_array($tag, $techTerms)) {
            $confidence += 15;
        }

        // Boost for content type tags
        $contentTypes = ['Tutorial', 'Review', 'Guide', 'News', 'Tips'];
        if (in_array($tag, $contentTypes)) {
            $confidence += 10;
        }

        return min(100, $confidence);
    }

    /**
     * Get reason for tag suggestion.
     */
    protected function getTagSuggestionReason(string $tag): string
    {
        if (BlogTag::where('name', $tag)->exists()) {
            return "Matches existing tag";
        }

        $techTerms = config('blog.ai.tech_terms', []);
        if (in_array($tag, $techTerms)) {
            return "Technology mentioned in content";
        }

        return "Extracted from content analysis";
    }

    /**
     * Calculate string similarity between two strings.
     */
    protected function calculateStringSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);

        if ($str1 === $str2) {
            return 1.0;
        }

        // Use similar_text for basic similarity
        similar_text($str1, $str2, $percent);
        return $percent / 100;
    }
}