<?php

namespace App\Features\Blog\Services\AI;

use App\Features\Blog\Contracts\AIContentAnalyzerInterface;
use App\Features\Blog\Services\BlogContentAnalyzer;

class BasicContentAnalyzer implements AIContentAnalyzerInterface
{
    protected BlogContentAnalyzer $analyzer;

    public function __construct(BlogContentAnalyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    public function analyzeContent(string $title, string $content): array
    {
        return $this->analyzer->analyzeContent($title, $content);
    }

    public function getEstimatedCost(string $title, string $content): float
    {
        return 0.0; // Free
    }

    public function isAvailable(): bool
    {
        return true; // Always available
    }

    public function getName(): string
    {
        return 'Basic Analysis';
    }

    public function getEstimatedTime(): int
    {
        return 1; // 1 second
    }

    public function getQualityRating(): int
    {
        return 2; // 2/5 stars
    }
}