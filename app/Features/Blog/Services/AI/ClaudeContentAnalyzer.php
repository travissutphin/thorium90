<?php

namespace App\Features\Blog\Services\AI;

use App\Features\Blog\Contracts\AIContentAnalyzerInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClaudeContentAnalyzer implements AIContentAnalyzerInterface
{
    protected string $apiKey;
    protected string $model;
    protected float $costPerToken;

    public function __construct()
    {
        $this->apiKey = env('CLAUDE_API_KEY');
        $this->model = env('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022');
        $this->costPerToken = (float) env('CLAUDE_COST_PER_TOKEN', 0.000003);
    }

    public function analyzeContent(string $title, string $content): array
    {
        // Check cache first
        $cacheKey = 'ai_analysis_' . md5($title . $content . 'claude');
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $prompt = $this->buildPrompt($title, $content);
            
            // Log the API call for debugging
            Log::info('Claude API call initiated', [
                'title' => $title,
                'content_length' => strlen($content),
                'estimated_cost' => $this->getEstimatedCost($title, $content),
                'timestamp' => now()
            ]);
            
            $response = $this->callClaudeAPI($prompt);
            $analysis = $this->parseResponse($response);
            
            // Log successful analysis
            Log::info('Claude API analysis completed', [
                'title' => $title,
                'tags_count' => count($analysis['suggestions']['tags'] ?? []),
                'keywords_count' => count($analysis['suggestions']['keywords'] ?? [])
            ]);
            
            // Cache result for 7 days
            Cache::put($cacheKey, $analysis, now()->addDays(7));
            
            return $analysis;
            
        } catch (\Exception $e) {
            Log::error('Claude API analysis failed', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);
            
            // Fallback to basic analysis
            return $this->getFallbackAnalysis($title, $content);
        }
    }

    public function getEstimatedCost(string $title, string $content): float
    {
        $estimatedTokens = str_word_count($title . ' ' . $content) * 1.3; // Rough estimate
        return $estimatedTokens * $this->costPerToken;
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && env('CLAUDE_ENABLED', false);
    }

    public function getName(): string
    {
        return 'Claude AI Analysis';
    }

    public function getEstimatedTime(): int
    {
        return 4; // 3-5 seconds
    }

    public function getQualityRating(): int
    {
        return 5; // 5/5 stars
    }

    protected function buildPrompt(string $title, string $content): string
    {
        $wordCount = str_word_count(strip_tags($content));
        
        return "
You are an SEO and content optimization expert. Analyze this blog post and provide highly relevant, actionable suggestions for unified SEO optimization.

TITLE: {$title}

CONTENT: {$content}

OPTIMIZATION REQUIREMENTS:

1. **Unified Keywords**: Create a comprehensive keyword strategy that includes:
   - Primary keywords (high search volume, direct match to content)
   - Secondary keywords (supporting topics and related terms)
   - Long-tail keywords (specific phrases users would search for)
   - Question-based keywords (for voice search and answer engines)

2. **Enhanced Tags**: Suggest tags with SEO context and confidence scoring. Focus on specific, actionable tags that aid discoverability.

3. **Content Classification**: Determine the optimal content type and provide quality metrics.

4. **Answer Engine Optimization**: Generate FAQ content that addresses common user questions related to the topic.

Provide ONLY a valid JSON response in exactly this format:

{
  \"keywords\": [
    {
      \"term\": \"exact keyword phrase\",
      \"type\": \"primary|secondary|long_tail|question\",
      \"confidence\": 90,
      \"search_intent\": \"informational|navigational|commercial|transactional\",
      \"reason\": \"why this keyword is valuable\",
      \"search_volume\": \"high|medium|low\"
    }
  ],
  \"tags\": [
    {
      \"name\": \"specific_tag_from_content\",
      \"confidence\": 85,
      \"seo_weight\": 0.8,
      \"reason\": \"mentioned prominently and relevant for categorization\"
    }
  ],
  \"topics\": [
    {
      \"name\": \"broad_topic_theme\",
      \"confidence\": 80,
      \"relevance\": \"core|supporting|peripheral\",
      \"reason\": \"central theme of the content\"
    }
  ],
  \"faqs\": [
    {
      \"question\": \"What is X?\",
      \"answer\": \"Based on content: X is...\",
      \"confidence\": 75,
      \"search_intent\": \"informational\",
      \"type\": \"generated\"
    }
  ],
  \"content_analysis\": {
    \"content_type\": \"blog_post|tutorial|review|news|guide|analysis\",
    \"reading_time\": {$this->calculateReadingTime($wordCount)},
    \"quality_score\": 82,
    \"seo_score\": 75,
    \"readability_score\": 78,
    \"keyword_density\": 2.3
  },
  \"optimization_suggestions\": [
    \"Specific actionable improvement for better SEO performance\"
  ],
  \"schema_recommendations\": [
    \"BlogPosting\",
    \"HowTo\"
  ]
}

CRITICAL REQUIREMENTS:
- Provide 8-12 strategic keywords across all types
- Include 2-3 question-based keywords for voice search
- Suggest 5-8 highly relevant tags with confidence scores
- Generate 2-4 FAQs that directly address user search intent
- Base ALL suggestions on ACTUAL content analysis, not generic assumptions
- Focus on search intent matching and user value
";
    }
    
    protected function calculateReadingTime(int $wordCount): int
    {
        return max(1, round($wordCount / 200)); // 200 words per minute
    }

    protected function callClaudeAPI(string $prompt): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'content-type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 2000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API request failed: ' . $response->body());
        }

        return $response->json();
    }

    protected function parseResponse(array $response): array
    {
        $content = $response['content'][0]['text'] ?? '';
        
        // Extract JSON from response
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $jsonData = json_decode($matches[0], true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                // Extract content analysis data
                $contentAnalysis = $jsonData['content_analysis'] ?? [];
                
                return [
                    'confidence' => $contentAnalysis['quality_score'] ?? 85,
                    'suggestions' => [
                        'tags' => $jsonData['tags'] ?? [],
                        'keywords' => $jsonData['keywords'] ?? [],
                        'topics' => $jsonData['topics'] ?? [],
                        'faqs' => $jsonData['faqs'] ?? [],
                        'content_type' => $contentAnalysis['content_type'] ?? 'blog_post',
                        'reading_time' => $contentAnalysis['reading_time'] ?? 3,
                        'schema_recommendations' => $jsonData['schema_recommendations'] ?? ['BlogPosting'],
                    ],
                    'metadata' => [
                        'word_count' => str_word_count(strip_tags($content)),
                        'analyzed_at' => now(),
                        'analyzer' => 'claude',
                        'quality_score' => $contentAnalysis['quality_score'] ?? 85,
                        'seo_score' => $contentAnalysis['seo_score'] ?? 75,
                        'readability_score' => $contentAnalysis['readability_score'] ?? 80,
                        'keyword_density' => $contentAnalysis['keyword_density'] ?? 2.0,
                        'improvements' => $jsonData['optimization_suggestions'] ?? [],
                    ]
                ];
            }
        }

        throw new \Exception('Failed to parse Claude API response');
    }

    protected function getFallbackAnalysis(string $title, string $content): array
    {
        // Fallback to basic analysis if AI fails
        $basicAnalyzer = app(\App\Features\Blog\Services\AI\BasicContentAnalyzer::class);
        $result = $basicAnalyzer->analyzeContent($title, $content);
        
        // Mark as fallback
        $result['metadata']['analyzer'] = 'basic_fallback';
        $result['metadata']['note'] = 'AI analysis failed, using basic analysis';
        
        return $result;
    }
}