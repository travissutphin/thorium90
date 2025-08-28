<?php

namespace App\Features\Blog\Services;

use Illuminate\Support\Str;

class ContentClassifier
{
    /**
     * Classification patterns for different content types.
     */
    protected array $classificationPatterns = [
        'tutorial' => [
            'title_patterns' => [
                '/\b(how to|tutorial|guide|step by step|learn|build|create|setup|install)\b/i',
                '/\b(building|creating|making|developing|implementing)\b/i'
            ],
            'content_patterns' => [
                '/\b(step \d+|first,|second,|third,|next,|then,|finally,|installation)\b/i',
                '/\b(let\'s|we\'ll|you\'ll|we will|you will)\b/i'
            ],
            'structure_indicators' => ['numbered_lists', 'step_sequence'],
            'weight' => 3
        ],
        'review' => [
            'title_patterns' => [
                '/\b(review|comparison|vs|versus|compare|analysis|evaluation)\b/i',
                '/\b(best|top \d+|rating|benchmark)\b/i'
            ],
            'content_patterns' => [
                '/\b(pros|cons|advantages|disadvantages|rating|score|performance)\b/i',
                '/\b(recommend|not recommend|better|worse|superior|inferior)\b/i'
            ],
            'structure_indicators' => ['comparison_table', 'rating_system'],
            'weight' => 3
        ],
        'news' => [
            'title_patterns' => [
                '/\b(news|announced|released|update|launch|breaking|latest)\b/i',
                '/\b(2024|2025|just|recently|today|yesterday)\b/i'
            ],
            'content_patterns' => [
                '/\b(announced|released|launched|unveiled|introduced|yesterday|today)\b/i',
                '/\b(according to|sources|reports|official|statement)\b/i'
            ],
            'structure_indicators' => ['date_references', 'source_citations'],
            'weight' => 2
        ],
        'guide' => [
            'title_patterns' => [
                '/\b(guide|handbook|manual|reference|complete|comprehensive|ultimate)\b/i',
                '/\b(introduction|getting started|beginner|basics)\b/i'
            ],
            'content_patterns' => [
                '/\b(overview|introduction|chapter|section|fundamentals|concepts)\b/i',
                '/\b(understand|learn|know|important|essential|key)\b/i'
            ],
            'structure_indicators' => ['table_of_contents', 'section_headers'],
            'weight' => 2
        ],
        'analysis' => [
            'title_patterns' => [
                '/\b(analysis|deep dive|exploration|investigation|study|research)\b/i',
                '/\b(why|understanding|behind|theory|concept)\b/i'
            ],
            'content_patterns' => [
                '/\b(analyze|examine|investigate|research|study|conclusion)\b/i',
                '/\b(hypothesis|theory|evidence|findings|results|data)\b/i'
            ],
            'structure_indicators' => ['data_points', 'conclusions'],
            'weight' => 2
        ],
        'blog_post' => [
            'title_patterns' => [
                '/\b(thoughts|opinion|experience|story|journey|reflection)\b/i',
                '/\b(my|our|personal|sharing|lessons learned)\b/i'
            ],
            'content_patterns' => [
                '/\b(i think|in my opinion|personally|i believe|experience|learned)\b/i',
                '/\b(recently|last week|yesterday|today|when i)\b/i'
            ],
            'structure_indicators' => ['personal_pronouns', 'anecdotes'],
            'weight' => 1
        ]
    ];

    /**
     * Classify content type based on title and content analysis.
     */
    public function classify(string $title, string $content): string
    {
        $scores = [];
        
        foreach ($this->classificationPatterns as $type => $patterns) {
            $score = $this->calculateTypeScore($title, $content, $patterns);
            $scores[$type] = $score;
        }

        // Sort by score and return the highest scoring type
        arsort($scores);
        $topType = array_key_first($scores);
        $topScore = $scores[$topType];

        // If no strong classification, default to blog_post
        if ($topScore < 3) {
            return 'blog_post';
        }

        return $topType;
    }

    /**
     * Calculate confidence score for a specific content type.
     */
    protected function calculateTypeScore(string $title, string $content, array $patterns): float
    {
        $score = 0;
        $titleLower = strtolower($title);
        $contentLower = strtolower($content);
        
        // Score title patterns
        foreach ($patterns['title_patterns'] as $pattern) {
            if (preg_match($pattern, $titleLower)) {
                $score += $patterns['weight'];
            }
        }

        // Score content patterns
        foreach ($patterns['content_patterns'] as $pattern) {
            $matches = preg_match_all($pattern, $contentLower);
            if ($matches > 0) {
                $score += min($matches, 3) * ($patterns['weight'] * 0.5);
            }
        }

        // Score structure indicators
        $structureScore = $this->analyzeStructure($content, $patterns['structure_indicators']);
        $score += $structureScore;

        return $score;
    }

    /**
     * Analyze content structure for type indicators.
     */
    protected function analyzeStructure(string $content, array $indicators): float
    {
        $score = 0;

        foreach ($indicators as $indicator) {
            switch ($indicator) {
                case 'numbered_lists':
                    if (preg_match('/\b\d+\.\s+/', $content)) {
                        $score += 1;
                    }
                    break;

                case 'step_sequence':
                    if (preg_match_all('/\b(step \d+|first,|second,|third,|next,|then,|finally)/i', $content) >= 3) {
                        $score += 1.5;
                    }
                    break;

                case 'comparison_table':
                    if (preg_match('/<table|<th|<td/', $content)) {
                        $score += 1;
                    }
                    break;

                case 'rating_system':
                    if (preg_match('/\b(\d+\/\d+|\d+\.\d+\/\d+|★|stars?|rating)/i', $content)) {
                        $score += 1;
                    }
                    break;

                case 'date_references':
                    if (preg_match('/\b(january|february|march|april|may|june|july|august|september|october|november|december|\d{4})/i', $content)) {
                        $score += 0.5;
                    }
                    break;

                case 'source_citations':
                    if (preg_match('/\b(source|according to|via|citation|reference)/i', $content)) {
                        $score += 0.5;
                    }
                    break;

                case 'table_of_contents':
                    if (preg_match('/table of contents|contents:/i', $content)) {
                        $score += 1;
                    }
                    break;

                case 'section_headers':
                    $headerCount = preg_match_all('/<h[2-6]/', $content);
                    if ($headerCount >= 3) {
                        $score += 1;
                    }
                    break;

                case 'data_points':
                    if (preg_match_all('/\b\d+%|\$\d+|\d+ users?|\d+ times?/i', $content) >= 2) {
                        $score += 1;
                    }
                    break;

                case 'conclusions':
                    if (preg_match('/\b(conclusion|summary|results?|findings?|takeaways?)/i', $content)) {
                        $score += 0.5;
                    }
                    break;

                case 'personal_pronouns':
                    $pronounCount = preg_match_all('/\b(i|my|me|our|we|us)\b/i', $content);
                    if ($pronounCount >= 5) {
                        $score += 0.5;
                    }
                    break;

                case 'anecdotes':
                    if (preg_match('/\b(story|experience|happened|remember|once)/i', $content)) {
                        $score += 0.5;
                    }
                    break;
            }
        }

        return $score;
    }

    /**
     * Get all available content types with descriptions.
     */
    public function getAvailableTypes(): array
    {
        return [
            'tutorial' => [
                'name' => 'Tutorial',
                'description' => 'Step-by-step instructional content',
                'icon' => 'book-open'
            ],
            'review' => [
                'name' => 'Review',
                'description' => 'Product or service evaluation',
                'icon' => 'star'
            ],
            'news' => [
                'name' => 'News',
                'description' => 'Latest updates and announcements',
                'icon' => 'newspaper'
            ],
            'guide' => [
                'name' => 'Guide',
                'description' => 'Comprehensive reference material',
                'icon' => 'map'
            ],
            'analysis' => [
                'name' => 'Analysis',
                'description' => 'In-depth examination and research',
                'icon' => 'search'
            ],
            'blog_post' => [
                'name' => 'Blog Post',
                'description' => 'General blog content and opinions',
                'icon' => 'edit'
            ]
        ];
    }
}