<?php

namespace App\Features\Blog\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Features\Blog\Models\BlogTag;
use App\Features\Blog\Models\BlogCategory;

class BlogContentAnalyzer
{
    protected KeywordExtractor $keywordExtractor;
    protected TagSuggestionEngine $tagSuggestionEngine;
    protected FAQDetector $faqDetector;
    protected ContentClassifier $contentClassifier;

    public function __construct(
        KeywordExtractor $keywordExtractor,
        TagSuggestionEngine $tagSuggestionEngine,
        FAQDetector $faqDetector,
        ContentClassifier $contentClassifier
    ) {
        $this->keywordExtractor = $keywordExtractor;
        $this->tagSuggestionEngine = $tagSuggestionEngine;
        $this->faqDetector = $faqDetector;
        $this->contentClassifier = $contentClassifier;
    }

    /**
     * Analyze blog content and return comprehensive suggestions.
     */
    public function analyzeContent(string $title, string $content): array
    {
        $analysis = [
            'confidence' => 0,
            'suggestions' => [
                'tags' => [],
                'keywords' => [],
                'topics' => [],
                'faqs' => [],
                'content_type' => null,
                'reading_time' => null,
            ],
            'metadata' => [
                'word_count' => str_word_count(strip_tags($content)),
                'analyzed_at' => now(),
            ]
        ];

        // Extract keywords
        $keywords = $this->keywordExtractor->extract($title, $content);
        $analysis['suggestions']['keywords'] = $keywords->map(function ($keyword) {
            return [
                'name' => $keyword,
                'confidence' => rand(60, 90), // Simple confidence for MVP
                'reason' => 'Extracted from content'
            ];
        })->toArray();

        // Suggest tags
        $tagSuggestions = $this->tagSuggestionEngine->suggestTags($title, $content, $keywords);
        $analysis['suggestions']['tags'] = $tagSuggestions->values()->toArray();

        // Extract topics
        $topics = $this->extractTopics($title, $content);
        $analysis['suggestions']['topics'] = $topics->map(function ($topic) {
            return [
                'name' => $topic,
                'confidence' => rand(65, 85), // Simple confidence for MVP
                'reason' => 'Identified in content'
            ];
        })->values()->toArray();

        // Detect FAQs
        $faqs = $this->faqDetector->detectFAQs($content);
        $analysis['suggestions']['faqs'] = $faqs->values()->toArray();

        // Classify content type
        $contentType = $this->contentClassifier->classify($title, $content);
        $analysis['suggestions']['content_type'] = $contentType;

        // Calculate reading time
        $analysis['suggestions']['reading_time'] = $this->calculateReadingTime($content);

        // Calculate overall confidence
        $analysis['confidence'] = $this->calculateConfidence($analysis);

        return $analysis;
    }

    /**
     * Extract topics from content using keyword analysis and context.
     */
    protected function extractTopics(string $title, string $content): Collection
    {
        $topics = collect();
        $combinedText = $title . ' ' . $content;

        // Technology/framework detection
        $techTerms = config('blog.ai.tech_terms', [
            'Laravel', 'PHP', 'JavaScript', 'Vue.js', 'React', 'Node.js',
            'Docker', 'MySQL', 'Redis', 'API', 'REST', 'GraphQL',
            'Authentication', 'Security', 'Performance', 'Testing',
            // Business & Soft Skills
            'Coaching', 'Leadership', 'Management', 'Communication', 'Business',
            'Productivity', 'Professional Development', 'Team Building', 'Psychology',
            'Self-Improvement', 'Education', 'Sales', 'Marketing', 'Strategy'
        ]);

        foreach ($techTerms as $term) {
            if (stripos($combinedText, $term) !== false) {
                $topics->push($term);
            }
        }

        // Extract topics from keywords that are already detected
        $extractedKeywords = $this->keywordExtractor->extract($title, $content);
        $businessKeywords = ['coaching', 'leadership', 'management', 'communication', 'business', 'questions', 'habits', 'development'];
        
        foreach ($extractedKeywords as $keyword) {
            $keywordLower = strtolower($keyword);
            foreach ($businessKeywords as $business) {
                if (stripos($keywordLower, $business) !== false || stripos($business, $keywordLower) !== false) {
                    $topics->push(ucfirst($business));
                    break;
                }
            }
        }

        // Extract topics from headings
        if (preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $content, $matches)) {
            foreach ($matches[1] as $heading) {
                $cleanHeading = strip_tags($heading);
                if (strlen($cleanHeading) > 5 && strlen($cleanHeading) < 50) {
                    $topics->push($cleanHeading);
                }
            }
        }

        return $topics->unique()->take(5);
    }

    /**
     * Calculate reading time based on word count.
     */
    protected function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $wordsPerMinute = config('blog.settings.reading_words_per_minute', 200);
        
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Calculate overall confidence score for the analysis.
     */
    protected function calculateConfidence(array $analysis): int
    {
        $confidence = 0;
        $factors = 0;

        // Factor 1: Keywords detected
        if (count($analysis['suggestions']['keywords']) > 0) {
            $confidence += min(30, count($analysis['suggestions']['keywords']) * 5);
        }
        $factors++;

        // Factor 2: Tags suggested
        if (count($analysis['suggestions']['tags']) > 0) {
            $confidence += min(25, count($analysis['suggestions']['tags']) * 5);
        }
        $factors++;

        // Factor 3: Topics identified
        if (count($analysis['suggestions']['topics']) > 0) {
            $confidence += min(20, count($analysis['suggestions']['topics']) * 4);
        }
        $factors++;

        // Factor 4: Content length adequacy
        $wordCount = $analysis['metadata']['word_count'];
        if ($wordCount > 300) {
            $confidence += 15;
        } elseif ($wordCount > 100) {
            $confidence += 10;
        }
        $factors++;

        // Factor 5: FAQs detected
        if (count($analysis['suggestions']['faqs']) > 0) {
            $confidence += 10;
        }
        $factors++;

        return min(100, $confidence);
    }
}