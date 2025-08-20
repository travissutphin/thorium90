import React, { useMemo } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Clock, BookOpen, TrendingUp, Info } from 'lucide-react';

interface ReadingTimeDisplayProps {
    content: string;
    readingTime?: number; // Manual override
    wordsPerMinute?: number;
    showWordCount?: boolean;
    showDetails?: boolean;
}

/**
 * ReadingTimeDisplay Component
 * 
 * Calculates and displays estimated reading time for content, which is
 * automatically included in schema.org markup for enhanced AEO.
 * 
 * Features:
 * - Automatic reading time calculation
 * - Word count analysis
 * - Content length categorization
 * - Schema.org timeRequired property
 * - User experience insights
 * 
 * AEO Integration:
 * - Generates ISO 8601 duration format (PT5M)
 * - Enhances content metadata
 * - Improves user engagement metrics
 */
export function ReadingTimeDisplay({ 
    content, 
    readingTime, 
    wordsPerMinute = 200, 
    showWordCount = true, 
    showDetails = false 
}: ReadingTimeDisplayProps) {
    
    const analysis = useMemo(() => {
        // Clean content of HTML tags and extra whitespace
        const cleanContent = content
            .replace(/<[^>]*>/g, ' ') // Remove HTML tags
            .replace(/\s+/g, ' ') // Normalize whitespace
            .trim();
        
        const words = cleanContent.split(' ').filter(word => word.length > 0);
        const wordCount = words.length;
        
        // Calculate reading time (override if provided)
        const calculatedMinutes = Math.max(1, Math.ceil(wordCount / wordsPerMinute));
        const finalReadingTime = readingTime || calculatedMinutes;
        
        // Determine content length category
        let contentCategory: 'short' | 'medium' | 'long' | 'very_long';
        if (wordCount < 300) contentCategory = 'short';
        else if (wordCount < 1000) contentCategory = 'medium';
        else if (wordCount < 2500) contentCategory = 'long';
        else contentCategory = 'very_long';
        
        // Generate ISO 8601 duration for schema.org
        const isoDuration = `PT${finalReadingTime}M`;
        
        // Calculate average words per sentence (readability indicator)
        const sentences = cleanContent.split(/[.!?]+/).filter(s => s.trim().length > 0);
        const avgWordsPerSentence = sentences.length > 0 ? Math.round(wordCount / sentences.length) : 0;
        
        return {
            wordCount,
            readingTime: finalReadingTime,
            isoDuration,
            contentCategory,
            sentences: sentences.length,
            avgWordsPerSentence,
            readabilityScore: getReadabilityScore(avgWordsPerSentence, wordCount)
        };
    }, [content, readingTime, wordsPerMinute]);

    function getReadabilityScore(avgWordsPerSentence: number, wordCount: number): 'easy' | 'medium' | 'difficult' {
        // Simple readability scoring based on sentence length
        if (avgWordsPerSentence <= 15 && wordCount >= 100) return 'easy';
        if (avgWordsPerSentence <= 20) return 'medium';
        return 'difficult';
    }

    function getCategoryInfo(category: string) {
        switch (category) {
            case 'short':
                return {
                    label: 'Quick Read',
                    color: 'bg-green-100 text-green-800',
                    description: 'Perfect for quick consumption and sharing'
                };
            case 'medium':
                return {
                    label: 'Standard Read',
                    color: 'bg-blue-100 text-blue-800',
                    description: 'Ideal length for detailed explanations'
                };
            case 'long':
                return {
                    label: 'In-depth Read',
                    color: 'bg-orange-100 text-orange-800',
                    description: 'Comprehensive coverage of the topic'
                };
            case 'very_long':
                return {
                    label: 'Extensive Read',
                    color: 'bg-red-100 text-red-800',
                    description: 'Consider breaking into sections'
                };
            default:
                return {
                    label: 'Unknown',
                    color: 'bg-gray-100 text-gray-800',
                    description: 'Content analysis unavailable'
                };
        }
    }

    function getReadabilityInfo(score: string) {
        switch (score) {
            case 'easy':
                return {
                    label: 'Easy to Read',
                    color: 'bg-green-100 text-green-800',
                    icon: <TrendingUp className="h-3 w-3" />
                };
            case 'medium':
                return {
                    label: 'Moderate Reading',
                    color: 'bg-yellow-100 text-yellow-800',
                    icon: <BookOpen className="h-3 w-3" />
                };
            case 'difficult':
                return {
                    label: 'Complex Reading',
                    color: 'bg-red-100 text-red-800',
                    icon: <BookOpen className="h-3 w-3" />
                };
            default:
                return {
                    label: 'Unknown',
                    color: 'bg-gray-100 text-gray-800',
                    icon: <BookOpen className="h-3 w-3" />
                };
        }
    }

    const categoryInfo = getCategoryInfo(analysis.contentCategory);
    const readabilityInfo = getReadabilityInfo(analysis.readabilityScore);

    if (analysis.wordCount === 0) {
        return (
            <Card className="w-full">
                <CardContent className="p-4">
                    <div className="flex items-center space-x-2 text-muted-foreground">
                        <Clock className="h-4 w-4" />
                        <span className="text-sm">Add content to see reading time</span>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card className="w-full">
            <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Clock className="h-5 w-5 text-indigo-600" />
                        <CardTitle className="text-base">Reading Time Analysis</CardTitle>
                    </div>
                    <Badge variant="secondary" className="flex items-center space-x-1">
                        <Clock className="h-3 w-3" />
                        <span>{analysis.readingTime} min read</span>
                    </Badge>
                </div>
                <CardDescription>
                    Estimated reading time automatically included in schema markup for better AEO
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4">
                {/* Primary Stats */}
                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-1">
                        <div className="text-2xl font-bold text-indigo-600">
                            {analysis.readingTime}
                        </div>
                        <div className="text-sm text-muted-foreground">
                            minute{analysis.readingTime > 1 ? 's' : ''} to read
                        </div>
                    </div>
                    
                    {showWordCount && (
                        <div className="space-y-1">
                            <div className="text-2xl font-bold text-gray-700">
                                {analysis.wordCount.toLocaleString()}
                            </div>
                            <div className="text-sm text-muted-foreground">
                                word{analysis.wordCount > 1 ? 's' : ''}
                            </div>
                        </div>
                    )}
                </div>

                {/* Content Category */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <BookOpen className="h-4 w-4 text-muted-foreground" />
                        <span className="text-sm text-muted-foreground">Content Length:</span>
                    </div>
                    <Badge className={categoryInfo.color}>
                        {categoryInfo.label}
                    </Badge>
                </div>

                {/* Readability Score */}
                {showDetails && (
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-2">
                            {readabilityInfo.icon}
                            <span className="text-sm text-muted-foreground">Readability:</span>
                        </div>
                        <Badge className={readabilityInfo.color}>
                            {readabilityInfo.label}
                        </Badge>
                    </div>
                )}

                {/* Detailed Stats */}
                {showDetails && (
                    <div className="grid grid-cols-2 gap-4 pt-2 border-t border-border">
                        <div className="text-center">
                            <div className="text-lg font-semibold">{analysis.sentences}</div>
                            <div className="text-xs text-muted-foreground">Sentences</div>
                        </div>
                        <div className="text-center">
                            <div className="text-lg font-semibold">{analysis.avgWordsPerSentence}</div>
                            <div className="text-xs text-muted-foreground">Avg words/sentence</div>
                        </div>
                    </div>
                )}

                {/* Schema.org Info */}
                <Alert>
                    <Info className="h-4 w-4" />
                    <AlertDescription className="text-xs">
                        <strong>Schema Markup:</strong> Reading time will be included as <code className="bg-muted px-1 rounded text-xs">timeRequired: "{analysis.isoDuration}"</code> in your page's structured data.
                    </AlertDescription>
                </Alert>

                {/* Content Recommendations */}
                {showDetails && (
                    <div className="space-y-2">
                        <div className="text-sm font-medium">AEO Recommendations:</div>
                        <div className="space-y-1 text-xs text-muted-foreground">
                            {analysis.contentCategory === 'short' && (
                                <p>• Consider expanding content to 500+ words for better search visibility</p>
                            )}
                            {analysis.contentCategory === 'very_long' && (
                                <p>• Consider breaking into multiple sections or adding a table of contents</p>
                            )}
                            {analysis.readabilityScore === 'difficult' && (
                                <p>• Consider shorter sentences (avg: {analysis.avgWordsPerSentence} words) for better readability</p>
                            )}
                            {analysis.readingTime >= 10 && (
                                <p>• Long-form content performs well for topic authority and detailed search queries</p>
                            )}
                            <p>• Reading time helps users gauge content depth and improves engagement metrics</p>
                        </div>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

export default ReadingTimeDisplay;