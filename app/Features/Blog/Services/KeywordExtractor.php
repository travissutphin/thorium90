<?php

namespace App\Features\Blog\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class KeywordExtractor
{
    /**
     * Common stop words to filter out from keywords.
     */
    protected array $stopWords = [
        'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with',
        'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before',
        'after', 'above', 'below', 'between', 'among', 'through', 'against',
        'a', 'an', 'as', 'are', 'was', 'were', 'been', 'be', 'have', 'has',
        'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should',
        'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those',
        'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which', 'who',
        'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few',
        'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only',
        'own', 'same', 'so', 'than', 'too', 'very', 'just', 'now'
    ];

    /**
     * Extract keywords from title and content using TF-IDF-like approach.
     */
    public function extract(string $title, string $content): Collection
    {
        $titleKeywords = $this->extractFromText($title, 3.0); // Higher weight for title
        $contentKeywords = $this->extractFromText($content, 1.0);

        // Combine and score keywords
        $allKeywords = collect();

        foreach ($titleKeywords as $keyword => $score) {
            $allKeywords->put($keyword, $score);
        }

        foreach ($contentKeywords as $keyword => $score) {
            if ($allKeywords->has($keyword)) {
                $allKeywords->put($keyword, $allKeywords->get($keyword) + $score);
            } else {
                $allKeywords->put($keyword, $score);
            }
        }

        // Extract technical terms and proper nouns
        $technicalTerms = $this->extractTechnicalTerms($title . ' ' . $content);
        foreach ($technicalTerms as $term) {
            $score = $allKeywords->get($term, 0) + 2.0; // Boost technical terms
            $allKeywords->put($term, $score);
        }

        return $allKeywords
            ->sortByDesc(function ($score) {
                return $score;
            })
            ->take(10)
            ->keys();
    }

    /**
     * Extract keywords from text with basic TF-IDF scoring.
     */
    protected function extractFromText(string $text, float $weight = 1.0): Collection
    {
        // Clean and tokenize text
        $cleanText = $this->cleanText($text);
        $words = $this->tokenize($cleanText);

        // Count word frequencies
        $frequencies = $words->countBy();

        // Filter out stop words and short words
        $keywords = $frequencies->filter(function ($count, $word) {
            return !in_array(strtolower($word), $this->stopWords) 
                && strlen($word) > 2 
                && strlen($word) < 30
                && !is_numeric($word);
        });

        // Apply TF-IDF-like scoring
        $totalWords = $words->count();
        $scored = $keywords->map(function ($frequency, $word) use ($totalWords, $weight) {
            $tf = $frequency / $totalWords;
            $wordLength = strlen($word);
            
            // Boost score for longer words and proper capitalization
            $lengthBoost = min(2.0, $wordLength / 5);
            $capitalizationBoost = ctype_upper(substr($word, 0, 1)) ? 1.2 : 1.0;
            
            return $tf * $weight * $lengthBoost * $capitalizationBoost;
        });

        return $scored;
    }

    /**
     * Extract technical terms, frameworks, and proper nouns.
     */
    protected function extractTechnicalTerms(string $text): Collection
    {
        $terms = collect();

        // Predefined technical terms
        $techTerms = config('blog.ai.tech_terms', [
            'Laravel', 'PHP', 'JavaScript', 'TypeScript', 'Vue.js', 'React', 'Angular',
            'Node.js', 'Express', 'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP',
            'MySQL', 'PostgreSQL', 'Redis', 'MongoDB', 'Elasticsearch',
            'API', 'REST', 'GraphQL', 'JSON', 'XML', 'YAML',
            'Authentication', 'Authorization', 'JWT', 'OAuth', 'SAML',
            'Security', 'HTTPS', 'SSL', 'TLS', 'CSRF', 'XSS',
            'Performance', 'Optimization', 'Caching', 'CDN',
            'Testing', 'Unit Testing', 'Integration Testing', 'E2E',
            'CI/CD', 'Git', 'GitHub', 'GitLab', 'Bitbucket',
            'Webpack', 'Vite', 'npm', 'Composer', 'Packagist'
        ]);

        foreach ($techTerms as $term) {
            if (stripos($text, $term) !== false) {
                $terms->push($term);
            }
        }

        // Extract camelCase and PascalCase terms (likely technical)
        if (preg_match_all('/\b[a-z]+[A-Z][a-zA-Z]*\b/', $text, $matches)) {
            foreach ($matches[0] as $match) {
                if (strlen($match) > 3) {
                    $terms->push($match);
                }
            }
        }

        // Extract capitalized words (likely proper nouns)
        if (preg_match_all('/\b[A-Z][a-z]{2,}\b/', $text, $matches)) {
            foreach ($matches[0] as $match) {
                if (!in_array(strtolower($match), $this->stopWords)) {
                    $terms->push($match);
                }
            }
        }

        return $terms->unique()->filter(function ($term) {
            return strlen($term) > 2 && strlen($term) < 30;
        });
    }

    /**
     * Clean text by removing HTML tags and normalizing whitespace.
     */
    protected function cleanText(string $text): string
    {
        // Remove HTML tags
        $text = strip_tags($text);
        
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove special characters except letters, numbers, spaces, hyphens, and dots
        $text = preg_replace('/[^\w\s\-\.]/u', ' ', $text);
        
        return trim($text);
    }

    /**
     * Tokenize text into individual words.
     */
    protected function tokenize(string $text): Collection
    {
        return collect(preg_split('/\s+/', $text))
            ->filter(function ($word) {
                return !empty(trim($word));
            })
            ->map(function ($word) {
                return trim($word, '.,!?;:"\'()[]{}');
            })
            ->filter(function ($word) {
                return !empty($word);
            });
    }
}