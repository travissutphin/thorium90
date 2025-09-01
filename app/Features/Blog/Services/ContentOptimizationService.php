<?php

namespace App\Features\Blog\Services;

use App\Features\Blog\Contracts\AIContentAnalyzerInterface;
use App\Features\Blog\Models\BlogTag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ContentOptimizationService
{
    protected AIContentAnalyzerInterface $aiAnalyzer;
    
    public function __construct(AIContentAnalyzerInterface $aiAnalyzer)
    {
        $this->aiAnalyzer = $aiAnalyzer;
    }

    /**
     * Generate unified SEO optimization with AI + manual override capability
     */
    public function optimizeContent(array $data, array $options = []): array
    {
        $useAI = $options['use_ai'] ?? true;
        $manualOverrides = $options['manual_overrides'] ?? [];
        $schemaType = $data['schema_type'] ?? 'BlogPosting';
        
        // Get schema-specific configuration
        $schemaConfig = $this->getSchemaConfiguration($schemaType);
        
        $result = [
            'seo_keywords' => [],
            'enhanced_tags' => [],
            'optimization_data' => [
                'schema_type' => $schemaType,
                'content_type' => $schemaConfig['content_type'],
                'optimization_timestamp' => now()->toISOString(),
                'method' => $useAI ? 'ai_generated' : 'manual_only',
                'schema_requirements' => $schemaConfig['requirements']
            ],
            'ai_optimized_at' => null,
            'ai_model_used' => null
        ];

        // AI Generation Phase
        if ($useAI && $this->aiAnalyzer->isAvailable()) {
            try {
                $aiResult = $this->generateWithAI($data, $schemaConfig);
                $result = array_merge($result, $aiResult);
                
                Log::info('AI optimization completed', [
                    'schema_type' => $schemaType,
                    'keywords_generated' => count($aiResult['seo_keywords']),
                    'tags_generated' => count($aiResult['enhanced_tags'])
                ]);
                
            } catch (\Exception $e) {
                Log::warning('AI optimization failed, using fallback', [
                    'error' => $e->getMessage(),
                    'schema_type' => $schemaType
                ]);
                
                $result = array_merge($result, $this->getFallbackOptimization($data, $schemaConfig));
            }
        } else {
            // Manual-only mode
            $result = array_merge($result, $this->getManualOptimization($data, $schemaConfig));
        }

        // Apply Manual Overrides
        if (!empty($manualOverrides)) {
            $result = $this->applyManualOverrides($result, $manualOverrides);
        }

        // Validate and cleanup
        $result = $this->validateAndCleanup($result, $schemaConfig);

        return $result;
    }

    /**
     * Generate optimization using AI
     */
    protected function generateWithAI(array $data, array $schemaConfig): array
    {
        $analysis = $this->aiAnalyzer->analyzeContent($data['title'], $data['content'] ?? '');
        
        // Transform AI suggestions into unified structure
        $seoKeywords = $this->transformAIKeywords(
            $analysis['suggestions']['keywords'] ?? [],
            $analysis['suggestions']['topics'] ?? [],
            $schemaConfig
        );
        
        $enhancedTags = $this->transformAITags(
            $analysis['suggestions']['tags'] ?? [],
            $data['tags'] ?? []
        );
        
        $optimizationData = [
            'schema_type' => $data['schema_type'],
            'content_type' => $schemaConfig['content_type'],
            'ai_confidence' => $analysis['confidence'] ?? 85,
            'quality_score' => $analysis['metadata']['quality_score'] ?? 85,
            'seo_score' => $analysis['metadata']['seo_score'] ?? 75,
            'improvements' => $analysis['metadata']['improvements'] ?? [],
            'optimization_timestamp' => now()->toISOString(),
            'method' => 'ai_generated',
            'schema_requirements' => $schemaConfig['requirements']
        ];

        return [
            'seo_keywords' => $seoKeywords,
            'enhanced_tags' => $enhancedTags,
            'optimization_data' => $optimizationData,
            'ai_optimized_at' => now(),
            'ai_model_used' => $this->aiAnalyzer->getName()
        ];
    }

    /**
     * Transform AI keywords into unified structure
     */
    protected function transformAIKeywords(array $aiKeywords, array $aiTopics, array $schemaConfig): array
    {
        $keywords = [];
        
        // Process AI keywords (primary)
        foreach ($aiKeywords as $keyword) {
            if (is_array($keyword)) {
                $keywords[] = [
                    'term' => $keyword['name'] ?? $keyword['term'] ?? '',
                    'type' => 'primary',
                    'confidence' => $keyword['confidence'] ?? 85,
                    'search_intent' => $keyword['search_intent'] ?? 'informational',
                    'source' => 'ai',
                    'reason' => $keyword['reason'] ?? 'AI suggested'
                ];
            } else {
                $keywords[] = [
                    'term' => (string) $keyword,
                    'type' => 'primary',
                    'confidence' => 85,
                    'search_intent' => 'informational',
                    'source' => 'ai',
                    'reason' => 'AI suggested'
                ];
            }
        }
        
        // Process AI topics (secondary)
        foreach ($aiTopics as $topic) {
            if (is_array($topic)) {
                $keywords[] = [
                    'term' => $topic['name'] ?? $topic['term'] ?? '',
                    'type' => 'topic',
                    'confidence' => $topic['confidence'] ?? 75,
                    'search_intent' => 'informational',
                    'source' => 'ai',
                    'reason' => $topic['reason'] ?? 'Topic identified by AI'
                ];
            } else {
                $keywords[] = [
                    'term' => (string) $topic,
                    'type' => 'topic',
                    'confidence' => 75,
                    'search_intent' => 'informational', 
                    'source' => 'ai',
                    'reason' => 'Topic identified by AI'
                ];
            }
        }

        // Apply schema-specific keyword focus
        return $this->applySchemaKeywordFocus($keywords, $schemaConfig);
    }

    /**
     * Transform AI tags with enhanced context
     */
    protected function transformAITags(array $aiTags, array $existingTagIds): array
    {
        $enhancedTags = [];
        
        // Process existing tags
        if (!empty($existingTagIds)) {
            $tags = BlogTag::whereIn('id', $existingTagIds)->get();
            foreach ($tags as $tag) {
                $enhancedTags[] = [
                    'tag_id' => $tag->id,
                    'name' => $tag->name,
                    'seo_weight' => 0.7, // Default weight for manually selected tags
                    'ai_suggested' => false,
                    'confidence' => 100, // User selected
                    'source' => 'manual'
                ];
            }
        }
        
        // Process AI suggested tags
        foreach ($aiTags as $aiTag) {
            // Find or suggest creating tag
            $tagName = is_array($aiTag) ? ($aiTag['name'] ?? '') : (string) $aiTag;
            $confidence = is_array($aiTag) ? ($aiTag['confidence'] ?? 85) : 85;
            $reason = is_array($aiTag) ? ($aiTag['reason'] ?? 'AI suggested') : 'AI suggested';
            
            if ($tagName) {
                // Check if tag exists
                $existingTag = BlogTag::where('name', $tagName)->first();
                
                if ($existingTag) {
                    // Skip if already in manual selection
                    $alreadySelected = collect($enhancedTags)->contains('tag_id', $existingTag->id);
                    if (!$alreadySelected) {
                        $enhancedTags[] = [
                            'tag_id' => $existingTag->id,
                            'name' => $existingTag->name,
                            'seo_weight' => min(0.9, $confidence / 100),
                            'ai_suggested' => true,
                            'confidence' => $confidence,
                            'source' => 'ai',
                            'reason' => $reason
                        ];
                    }
                } else {
                    // Suggest new tag creation
                    $enhancedTags[] = [
                        'tag_id' => null,
                        'name' => $tagName,
                        'seo_weight' => min(0.8, $confidence / 100),
                        'ai_suggested' => true,
                        'confidence' => $confidence,
                        'source' => 'ai',
                        'reason' => $reason,
                        'requires_creation' => true
                    ];
                }
            }
        }
        
        return $enhancedTags;
    }

    /**
     * Apply schema-specific keyword focusing
     */
    protected function applySchemaKeywordFocus(array $keywords, array $schemaConfig): array
    {
        $focus = $schemaConfig['keyword_focus'] ?? 'broad';
        
        switch ($focus) {
            case 'instructional':
                // Boost how-to, tutorial, step-by-step keywords
                foreach ($keywords as &$keyword) {
                    if (preg_match('/\b(how\s+to|tutorial|guide|steps?|learn)\b/i', $keyword['term'])) {
                        $keyword['confidence'] = min(100, $keyword['confidence'] + 15);
                        $keyword['type'] = 'instructional_primary';
                    }
                }
                break;
                
            case 'question-based':
                // Boost question-format keywords for FAQ content
                foreach ($keywords as &$keyword) {
                    if (preg_match('/\b(what|how|why|when|where|which|who)\b/i', $keyword['term'])) {
                        $keyword['confidence'] = min(100, $keyword['confidence'] + 10);
                        $keyword['search_intent'] = 'question';
                    }
                }
                break;
        }
        
        return $keywords;
    }

    /**
     * Get fallback optimization when AI fails
     */
    protected function getFallbackOptimization(array $data, array $schemaConfig): array
    {
        // Extract basic keywords from title and content
        $text = ($data['title'] ?? '') . ' ' . ($data['content'] ?? '');
        $words = str_word_count(strtolower(strip_tags($text)), 1);
        
        // Simple keyword extraction
        $commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $keywords = [];
        
        $wordCounts = array_count_values($words);
        arsort($wordCounts);
        
        $i = 0;
        foreach ($wordCounts as $word => $count) {
            if ($i >= 10) break;
            if (strlen($word) > 3 && !in_array($word, $commonWords)) {
                $keywords[] = [
                    'term' => $word,
                    'type' => 'basic',
                    'confidence' => min(100, $count * 10),
                    'search_intent' => 'informational',
                    'source' => 'fallback',
                    'reason' => "Appears {$count} times in content"
                ];
                $i++;
            }
        }
        
        return [
            'seo_keywords' => $keywords,
            'enhanced_tags' => [], // No AI tags in fallback
            'optimization_data' => [
                'schema_type' => $data['schema_type'],
                'content_type' => $schemaConfig['content_type'],
                'optimization_timestamp' => now()->toISOString(),
                'method' => 'fallback',
                'note' => 'AI unavailable, using basic keyword extraction'
            ],
            'ai_optimized_at' => null,
            'ai_model_used' => null
        ];
    }

    /**
     * Get manual-only optimization
     */
    protected function getManualOptimization(array $data, array $schemaConfig): array
    {
        return [
            'seo_keywords' => [], // User will add manually
            'enhanced_tags' => [], // User will select manually
            'optimization_data' => [
                'schema_type' => $data['schema_type'],
                'content_type' => $schemaConfig['content_type'],
                'optimization_timestamp' => now()->toISOString(),
                'method' => 'manual_only',
                'schema_requirements' => $schemaConfig['requirements']
            ],
            'ai_optimized_at' => null,
            'ai_model_used' => null
        ];
    }

    /**
     * Apply manual overrides to AI suggestions
     */
    protected function applyManualOverrides(array $result, array $overrides): array
    {
        if (isset($overrides['seo_keywords'])) {
            // Merge manual keywords with AI suggestions, manual takes priority
            $manualKeywords = array_map(function($keyword) {
                return array_merge($keyword, ['source' => 'manual']);
            }, $overrides['seo_keywords']);
            
            $result['seo_keywords'] = array_merge($manualKeywords, $result['seo_keywords']);
            $result['optimization_data']['method'] = 'ai_with_manual_override';
        }
        
        if (isset($overrides['enhanced_tags'])) {
            $result['enhanced_tags'] = $overrides['enhanced_tags'];
            $result['optimization_data']['method'] = 'ai_with_manual_override';
        }
        
        return $result;
    }

    /**
     * Validate and cleanup the optimization result
     */
    protected function validateAndCleanup(array $result, array $schemaConfig): array
    {
        // Limit keywords to reasonable number
        $result['seo_keywords'] = array_slice($result['seo_keywords'], 0, 15);
        
        // Sort keywords by confidence
        usort($result['seo_keywords'], function($a, $b) {
            return ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0);
        });
        
        // Remove duplicate keywords
        $seen = [];
        $result['seo_keywords'] = array_filter($result['seo_keywords'], function($keyword) use (&$seen) {
            $term = strtolower($keyword['term'] ?? '');
            if (in_array($term, $seen) || empty($term)) {
                return false;
            }
            $seen[] = $term;
            return true;
        });
        
        // Limit enhanced tags
        $result['enhanced_tags'] = array_slice($result['enhanced_tags'], 0, 10);
        
        return $result;
    }

    /**
     * Get schema-specific configuration
     */
    protected function getSchemaConfiguration(string $schemaType): array
    {
        $mapping = config('blog.schema_mapping', []);
        
        if (isset($mapping[$schemaType])) {
            return $mapping[$schemaType];
        }
        
        // Default configuration
        return [
            'content_type' => 'blog_post',
            'keyword_focus' => 'broad',
            'requirements' => [],
            'optional_faqs' => false
        ];
    }
}