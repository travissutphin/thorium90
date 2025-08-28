<?php

namespace App\Features\Blog\Services\AI;

use App\Features\Blog\Contracts\AIContentAnalyzerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIAnalysisManager
{
    protected array $analyzers = [];
    protected string $defaultProvider;

    public function __construct()
    {
        $this->defaultProvider = config('ai.default_provider', 'basic');
        $this->registerAnalyzers();
    }

    /**
     * Get available analysis options for the user.
     */
    public function getAvailableAnalyzers(): array
    {
        $options = [];
        
        foreach ($this->analyzers as $key => $analyzer) {
            if ($analyzer->isAvailable()) {
                $options[$key] = [
                    'name' => $analyzer->getName(),
                    'quality_rating' => $analyzer->getQualityRating(),
                    'estimated_time' => $analyzer->getEstimatedTime(),
                    'cost' => 0, // Will be calculated per content
                ];
            }
        }

        return $options;
    }

    /**
     * Analyze content with specified provider.
     */
    public function analyzeContent(string $title, string $content, ?string $provider = null): array
    {
        $provider = $provider ?? $this->defaultProvider;
        $analyzer = $this->getAnalyzer($provider);
        
        if (!$analyzer || !$analyzer->isAvailable()) {
            Log::warning("AI analyzer not available: {$provider}, falling back to basic");
            $analyzer = $this->getAnalyzer('basic');
        }

        // Track usage
        $this->trackUsage($provider, $title, $content);

        try {
            return $analyzer->analyzeContent($title, $content);
        } catch (\Exception $e) {
            Log::error('AI analysis failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'title' => $title,
            ]);

            // Fallback to basic if enabled
            if (config('ai.features.fallback_enabled', true) && $provider !== 'basic') {
                return $this->analyzeContent($title, $content, 'basic');
            }

            throw $e;
        }
    }

    /**
     * Get cost estimate for analysis.
     */
    public function getAnalysisCost(string $title, string $content, string $provider): float
    {
        $analyzer = $this->getAnalyzer($provider);
        
        if (!$analyzer || !$analyzer->isAvailable()) {
            return 0.0;
        }

        return $analyzer->getEstimatedCost($title, $content);
    }

    /**
     * Check if user can perform AI analysis (within limits).
     */
    public function canUserAnalyze(int $userId): array
    {
        if (!config('ai.features.usage_tracking', true)) {
            return ['allowed' => true, 'reason' => 'tracking disabled'];
        }

        $monthlyLimit = config('ai.limits.per_user_monthly', 50);
        $costLimit = config('ai.limits.max_monthly_cost', 5.00);
        
        // Get current month usage
        $cacheKey = "ai_usage_{$userId}_" . now()->format('Y_m');
        $usage = Cache::get($cacheKey, ['count' => 0, 'cost' => 0.0]);

        if ($usage['count'] >= $monthlyLimit) {
            return [
                'allowed' => false, 
                'reason' => "Monthly limit reached ({$monthlyLimit} analyses)"
            ];
        }

        if ($usage['cost'] >= $costLimit) {
            return [
                'allowed' => false, 
                'reason' => sprintf("Monthly cost limit reached ($%.2f)", $costLimit)
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $monthlyLimit - $usage['count'],
            'remaining_cost' => $costLimit - $usage['cost'],
        ];
    }

    /**
     * Get user's current usage statistics.
     */
    public function getUserUsage(int $userId): array
    {
        $cacheKey = "ai_usage_{$userId}_" . now()->format('Y_m');
        $usage = Cache::get($cacheKey, ['count' => 0, 'cost' => 0.0]);
        
        $monthlyLimit = config('ai.limits.per_user_monthly', 50);
        $costLimit = config('ai.limits.max_monthly_cost', 5.00);

        return [
            'analyses_used' => $usage['count'],
            'analyses_limit' => $monthlyLimit,
            'cost_used' => $usage['cost'],
            'cost_limit' => $costLimit,
            'percentage_used' => round(($usage['count'] / $monthlyLimit) * 100, 1),
        ];
    }

    protected function registerAnalyzers(): void
    {
        // Basic analyzer (always available)
        $this->analyzers['basic'] = app(BasicContentAnalyzer::class);

        // Claude analyzer
        if (config('ai.providers.claude.enabled', false)) {
            $this->analyzers['claude'] = app(ClaudeContentAnalyzer::class);
        }

        // OpenAI analyzer (to be implemented)
        if (config('ai.providers.openai.enabled', false)) {
            // $this->analyzers['openai'] = app(OpenAIContentAnalyzer::class);
        }

        // Gemini analyzer (to be implemented)
        if (config('ai.providers.gemini.enabled', false)) {
            // $this->analyzers['gemini'] = app(GeminiContentAnalyzer::class);
        }
    }

    protected function getAnalyzer(string $provider): ?AIContentAnalyzerInterface
    {
        return $this->analyzers[$provider] ?? null;
    }

    protected function trackUsage(string $provider, string $title, string $content): void
    {
        if (!config('ai.features.usage_tracking', true)) {
            return;
        }

        // Skip tracking for basic analysis
        if ($provider === 'basic') {
            return;
        }

        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        $cost = $this->getAnalysisCost($title, $content, $provider);
        $cacheKey = "ai_usage_{$userId}_" . now()->format('Y_m');
        
        $usage = Cache::get($cacheKey, ['count' => 0, 'cost' => 0.0]);
        $usage['count']++;
        $usage['cost'] += $cost;
        
        // Cache until end of month
        $endOfMonth = now()->endOfMonth();
        Cache::put($cacheKey, $usage, $endOfMonth);

        Log::info('AI usage tracked', [
            'user_id' => $userId,
            'provider' => $provider,
            'cost' => $cost,
            'monthly_total' => $usage['cost'],
        ]);
    }
}