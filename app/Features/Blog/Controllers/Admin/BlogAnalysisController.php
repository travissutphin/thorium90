<?php

namespace App\Features\Blog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Features\Blog\Services\BlogContentAnalyzer;
use App\Features\Blog\Services\AI\AIAnalysisManager;
use App\Features\Blog\Services\ContentOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BlogAnalysisController extends Controller
{
    protected BlogContentAnalyzer $analyzer;
    protected AIAnalysisManager $aiManager;
    protected ContentOptimizationService $optimizationService;

    public function __construct(
        BlogContentAnalyzer $analyzer, 
        AIAnalysisManager $aiManager,
        ContentOptimizationService $optimizationService
    ) {
        $this->analyzer = $analyzer;
        $this->aiManager = $aiManager;
        $this->optimizationService = $optimizationService;
        
        // Apply permission middleware for blog content analysis
        $this->middleware('permission:blog.posts.create,blog.posts.edit');
    }

    /**
     * Analyze blog content and return suggestions.
     */
    public function analyzeContent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'content' => 'nullable|string|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $title = $request->input('title', '');
            $content = $request->input('content', '');

            logger()->info('Blog analysis request received', [
                'title' => $title,
                'content_length' => strlen($content),
                'user_id' => auth()->id(),
            ]);

            // Perform content analysis
            $analysis = $this->analyzer->analyzeContent($title, $content);

            logger()->info('Blog analysis completed successfully', [
                'tags_count' => count($analysis['suggestions']['tags'] ?? []),
                'keywords_count' => count($analysis['suggestions']['keywords'] ?? []),
                'topics_count' => count($analysis['suggestions']['topics'] ?? []),
                'confidence' => $analysis['confidence'] ?? 0,
            ]);

            $response = [
                'success' => true,
                'data' => [
                    'analysis' => $analysis,
                    'timestamp' => now(),
                ],
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            logger()->error('Blog content analysis failed', [
                'error' => $e->getMessage(),
                'title' => $request->input('title'),
                'content_length' => strlen($request->input('content', '')),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Content analysis failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get tag suggestions based on partial input.
     */
    public function suggestTags(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:65535',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $query = $request->input('query', '');
            $title = $request->input('title', '');
            $content = $request->input('content', '');
            $limit = $request->input('limit', 10);

            // If we have title/content, do full analysis
            if (!empty($title) || !empty($content)) {
                $analysis = $this->analyzer->analyzeContent($title, $content);
                $suggestions = collect($analysis['suggestions']['tags'])->take($limit);
            } else {
                // Simple query-based suggestions
                $suggestions = $this->getQueryBasedTagSuggestions($query, $limit);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'suggestions' => $suggestions,
                    'query' => $query,
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('Tag suggestion failed', [
                'error' => $e->getMessage(),
                'query' => $request->input('query'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tag suggestion failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Generate FAQ suggestions from content.
     */
    public function generateFAQs(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'content' => 'required|string|min:50|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $title = $request->input('title');
            $content = $request->input('content');

            $analysis = $this->analyzer->analyzeContent($title, $content);
            $faqs = collect($analysis['suggestions']['faqs']);

            return response()->json([
                'success' => true,
                'data' => [
                    'faqs' => $faqs,
                    'total_found' => $faqs->count(),
                    'confidence' => $analysis['confidence'],
                ],
            ]);

        } catch (\Exception $e) {
            logger()->error('FAQ generation failed', [
                'error' => $e->getMessage(),
                'title' => $request->input('title'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'FAQ generation failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get analysis statistics for admin dashboard.
     */
    public function getAnalysisStats(): JsonResponse
    {
        try {
            // This could be expanded with actual usage statistics
            $stats = [
                'total_analyses' => cache()->get('blog_analysis_count', 0),
                'successful_analyses' => cache()->get('blog_analysis_success_count', 0),
                'average_confidence' => cache()->get('blog_analysis_avg_confidence', 75),
                'top_suggested_tags' => cache()->get('blog_analysis_top_tags', []),
                'content_type_distribution' => cache()->get('blog_analysis_content_types', [
                    'tutorial' => 35,
                    'guide' => 25,
                    'blog_post' => 20,
                    'review' => 10,
                    'news' => 5,
                    'analysis' => 5,
                ]),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analysis statistics.',
            ], 500);
        }
    }

    /**
     * Get simple query-based tag suggestions.
     */
    protected function getQueryBasedTagSuggestions(string $query, int $limit): \Illuminate\Support\Collection
    {
        if (empty($query)) {
            // Return popular tags
            return \App\Features\Blog\Models\BlogTag::withCount('blogPosts')
                ->orderByDesc('blog_posts_count')
                ->limit($limit)
                ->get()
                ->map(function ($tag) {
                    return [
                        'name' => $tag->name,
                        'confidence' => 70,
                        'reason' => 'Popular existing tag',
                        'exists' => true,
                        'usage_count' => $tag->blog_posts_count,
                    ];
                });
        }

        // Search for matching tags
        $existingTags = \App\Features\Blog\Models\BlogTag::where('name', 'LIKE', "%{$query}%")
            ->withCount('blogPosts')
            ->orderByDesc('blog_posts_count')
            ->limit($limit)
            ->get()
            ->map(function ($tag) {
                return [
                    'name' => $tag->name,
                    'confidence' => 85,
                    'reason' => 'Matches existing tag',
                    'exists' => true,
                    'usage_count' => $tag->blog_posts_count,
                ];
            });

        // Add tech term suggestions if query matches
        $techTerms = collect(config('blog.ai.tech_terms', []))
            ->filter(function ($term) use ($query) {
                return stripos($term, $query) !== false;
            })
            ->take($limit - $existingTags->count())
            ->map(function ($term) {
                return [
                    'name' => $term,
                    'confidence' => 80,
                    'reason' => 'Technology term',
                    'exists' => \App\Features\Blog\Models\BlogTag::where('name', $term)->exists(),
                ];
            });

        return $existingTags->concat($techTerms);
    }

    /**
     * Get available AI analysis options.
     */
    public function getAnalysisOptions(): JsonResponse
    {
        $analyzers = $this->aiManager->getAvailableAnalyzers();
        $userUsage = $this->aiManager->getUserUsage(auth()->id());
        $canUseAI = $this->aiManager->canUserAnalyze(auth()->id());

        return response()->json([
            'analyzers' => $analyzers,
            'usage' => $userUsage,
            'can_use_ai' => $canUseAI,
        ]);
    }

    /**
     * Analyze content with specified AI provider.
     */
    public function analyzeWithAI(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'content' => 'required|string|min:' . config('ai.limits.min_content_length', 50),
            'provider' => 'required|string|in:basic,claude,openai,gemini',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $title = $request->input('title');
        $content = $request->input('content');
        $provider = $request->input('provider');

        // Check if user can perform AI analysis
        $canUseAI = $this->aiManager->canUserAnalyze(auth()->id());
        if (!$canUseAI['allowed'] && $provider !== 'basic') {
            return response()->json([
                'error' => 'AI analysis limit reached',
                'reason' => $canUseAI['reason'],
            ], 429);
        }

        try {
            // Get cost estimate
            $estimatedCost = $this->aiManager->getAnalysisCost($title, $content, $provider);
            
            // Perform analysis
            $result = $this->aiManager->analyzeContent($title, $content, $provider);
            
            // Add metadata about the analysis
            $result['metadata']['provider'] = $provider;
            $result['metadata']['cost'] = $estimatedCost;
            $result['metadata']['user_id'] = auth()->id();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Analysis failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cost estimate for AI analysis.
     */
    public function getAnalysisCost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'content' => 'required|string',
            'provider' => 'required|string|in:basic,claude,openai,gemini',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $title = $request->input('title');
        $content = $request->input('content');
        $provider = $request->input('provider');

        $cost = $this->aiManager->getAnalysisCost($title, $content, $provider);

        return response()->json([
            'provider' => $provider,
            'estimated_cost' => $cost,
            'formatted_cost' => '$' . number_format($cost, 4),
        ]);
    }

    /**
     * Get user's AI usage statistics.
     */
    public function getUserUsage(): JsonResponse
    {
        $usage = $this->aiManager->getUserUsage(auth()->id());
        $canUseAI = $this->aiManager->canUserAnalyze(auth()->id());

        return response()->json([
            'usage' => $usage,
            'limits' => $canUseAI,
        ]);
    }

    /**
     * Unified SEO optimization endpoint.
     * Combines AI analysis with manual overrides for comprehensive SEO optimization.
     */
    public function unifiedOptimization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'content' => 'nullable|string|min:20|max:65535',
            'schema_type' => 'required|string|in:' . implode(',', array_keys(config('blog.schema.available_types', []))),
            'use_ai' => 'boolean',
            'manual_overrides' => 'array',
            'manual_overrides.seo_keywords' => 'array',
            'manual_overrides.enhanced_tags' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'title' => $request->input('title'),
                'content' => $request->input('content', ''),
                'schema_type' => $request->input('schema_type', 'BlogPosting'),
            ];

            $options = [
                'use_ai' => $request->input('use_ai', true),
                'manual_overrides' => $request->input('manual_overrides', []),
            ];

            // Check AI usage limits if AI is requested
            if ($options['use_ai'] && $request->input('schema_type') !== 'basic') {
                $canUseAI = $this->aiManager->canUserAnalyze(auth()->id());
                if (!$canUseAI['allowed']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'AI analysis limit reached',
                        'reason' => $canUseAI['reason'],
                    ], 429);
                }
            }

            // Run unified optimization
            $result = $this->optimizationService->optimizeContent($data, $options);

            return response()->json([
                'success' => true,
                'optimization_data' => $result,
                'message' => 'Content optimized successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Unified optimization failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'title' => $request->input('title', ''),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Optimization failed',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred during optimization',
            ], 500);
        }
    }
}