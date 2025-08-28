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
        $this->apiKey = config('ai.providers.claude.api_key');
        $this->model = config('ai.providers.claude.model', 'claude-3-sonnet-20240229');
        $this->costPerToken = config('ai.providers.claude.cost_per_token', 0.000003);
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
        return !empty($this->apiKey) && config('ai.providers.claude.enabled', false);
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
You are a content analysis expert. Analyze this blog post and provide highly relevant, specific suggestions.

TITLE: {$title}

CONTENT: {$content}

ANALYSIS REQUIREMENTS:

1. **Tags**: Extract the most specific, relevant tags from the actual content. Avoid generic tags like 'blog', 'tips', 'guide'. Focus on technical terms, specific topics, tools, or concepts mentioned.

2. **Keywords**: Identify keywords with actual SEO potential that match what users would search for. Include both short-tail and long-tail keywords directly related to the content.

3. **Topics**: Identify the main themes/subjects covered in the content. Be specific (e.g., 'Laravel Eloquent ORM' not just 'PHP').

4. **Content Quality**: Base your scores on the actual content depth, structure, and usefulness.

Provide ONLY a valid JSON response in exactly this format:

{
  \"tags\": [
    {\"name\": \"specific_tag_from_content\", \"confidence\": 85, \"reason\": \"mentioned prominently in section X\"}
  ],
  \"keywords\": [
    {\"name\": \"exact_keyword_phrase\", \"confidence\": 90, \"reason\": \"high search volume potential\", \"search_intent\": \"informational\"}
  ],
  \"topics\": [
    {\"name\": \"specific_topic\", \"confidence\": 80, \"reason\": \"core theme of the article\"}
  ],
  \"faqs\": [
    {\"question\": \"What is X?\", \"answer\": \"Based on content: X is...\", \"confidence\": 75, \"type\": \"generated\"}
  ],
  \"content_type\": \"tutorial\",
  \"reading_time\": {$this->calculateReadingTime($wordCount)},
  \"quality_score\": 82,
  \"improvements\": [
    \"Specific actionable improvement\"
  ],
  \"seo_score\": 75
}

CRITICAL: Provide 5-8 highly relevant tags, 6-10 specific keywords, 3-5 focused topics. Base everything on the ACTUAL content, not generic assumptions.
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
                return [
                    'confidence' => $jsonData['quality_score'] ?? 85,
                    'suggestions' => [
                        'tags' => $jsonData['tags'] ?? [],
                        'keywords' => $jsonData['keywords'] ?? [],
                        'topics' => $jsonData['topics'] ?? [],
                        'faqs' => $jsonData['faqs'] ?? [],
                        'content_type' => $jsonData['content_type'] ?? 'blog_post',
                        'reading_time' => $jsonData['reading_time'] ?? 3,
                    ],
                    'metadata' => [
                        'word_count' => str_word_count($content),
                        'analyzed_at' => now(),
                        'analyzer' => 'claude',
                        'quality_score' => $jsonData['quality_score'] ?? 85,
                        'seo_score' => $jsonData['seo_score'] ?? 75,
                        'improvements' => $jsonData['improvements'] ?? [],
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