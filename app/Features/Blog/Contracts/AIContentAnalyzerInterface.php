<?php

namespace App\Features\Blog\Contracts;

interface AIContentAnalyzerInterface
{
    /**
     * Analyze content and return structured suggestions.
     */
    public function analyzeContent(string $title, string $content): array;

    /**
     * Get the estimated cost for this analysis.
     */
    public function getEstimatedCost(string $title, string $content): float;

    /**
     * Check if this analyzer is available and configured.
     */
    public function isAvailable(): bool;

    /**
     * Get the display name for this analyzer.
     */
    public function getName(): string;

    /**
     * Get the expected response time in seconds.
     */
    public function getEstimatedTime(): int;

    /**
     * Get the quality rating (1-5 stars).
     */
    public function getQualityRating(): int;
}