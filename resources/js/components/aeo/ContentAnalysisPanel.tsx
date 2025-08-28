import React, { useState, useCallback, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Lightbulb, 
    Sparkles, 
    Target, 
    HelpCircle, 
    RefreshCw, 
    CheckCircle, 
    AlertCircle,
    TrendingUp,
    Clock,
    Hash,
    MessageSquare,
    Zap,
    DollarSign,
    Star
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';

// Utility function to get CSRF token from various sources
const getCSRFToken = (): string => {
    // Try Inertia shared props first (most reliable)
    const inertiaPage = (window as any)?.page;
    if (inertiaPage?.props?.csrfToken) return inertiaPage.props.csrfToken;
    
    // Try meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (metaToken) return metaToken;
    
    // Try window objects (fallback)
    const windowToken = (window as any)?._token || (window as any)?.Laravel?.csrfToken;
    if (windowToken) return windowToken;
    
    console.warn('CSRF token not found in any location');
    return '';
};

// Debug function - can be called from browser console
(window as any).debugCSRF = () => {
    const inertiaPage = (window as any)?.page;
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const windowToken = (window as any)?._token || (window as any)?.Laravel?.csrfToken;
    
    console.log('🔍 CSRF Token Debug:');
    console.log('  Inertia props token:', inertiaPage?.props?.csrfToken ? 'Found' : 'Not found');
    console.log('  Meta tag token:', metaToken ? 'Found' : 'Not found');
    console.log('  Window token:', windowToken ? 'Found' : 'Not found');
    console.log('  Final token:', getCSRFToken() ? 'Available' : 'Not available');
};

export interface ContentAnalysisSuggestion {
    name: string;
    confidence: number;
    reason: string;
    exists?: boolean;
    usage_count?: number;
}

export interface ContentAnalysisResult {
    confidence: number;
    suggestions: {
        tags: ContentAnalysisSuggestion[];
        keywords: ContentAnalysisSuggestion[];
        topics: ContentAnalysisSuggestion[];
        faqs: Array<{
            question: string;
            answer: string;
            confidence: number;
            type: string;
        }>;
        content_type: string;
        reading_time: number;
    };
    metadata: {
        word_count: number;
        analyzed_at: string;
        provider?: string;
        cost?: number;
        quality_score?: number;
        seo_score?: number;
        improvements?: string[];
    };
}

export interface AnalysisProvider {
    key: string;
    name: string;
    quality_rating: number;
    estimated_time: number;
    cost: number;
    description: string;
}

export interface UserUsage {
    analyses_used: number;
    analyses_limit: number;
    cost_used: number;
    cost_limit: number;
    percentage_used: number;
}

export interface ContentAnalysisPanelProps {
    title: string;
    content: string;
    onTagsSelected?: (tags: ContentAnalysisSuggestion[]) => void;
    onKeywordsSelected?: (keywords: string[]) => void;
    onTopicsSelected?: (topics: string[]) => void;
    onFAQsSelected?: (faqs: Array<{question: string; answer: string}>) => void;
    onContentTypeSelected?: (contentType: string) => void;
    disabled?: boolean;
    className?: string;
}

export default function ContentAnalysisPanel({
    title,
    content,
    onTagsSelected,
    onKeywordsSelected,
    onTopicsSelected,
    onFAQsSelected,
    onContentTypeSelected,
    disabled = false,
    className
}: ContentAnalysisPanelProps) {
    const [analysis, setAnalysis] = useState<ContentAnalysisResult | null>(null);
    const [isAnalyzing, setIsAnalyzing] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [selectedTab, setSelectedTab] = useState('tags');
    const [providers, setProviders] = useState<AnalysisProvider[]>([]);
    const [selectedProvider, setSelectedProvider] = useState<string>('basic');
    const [userUsage, setUserUsage] = useState<UserUsage | null>(null);
    const [estimatedCost, setEstimatedCost] = useState<number>(0);
    const [isLoadingOptions, setIsLoadingOptions] = useState(false);
    const [selectedSuggestions, setSelectedSuggestions] = useState<{
        tags: ContentAnalysisSuggestion[];
        keywords: string[];
        topics: string[];
        faqs: Array<{question: string; answer: string}>;
    }>({
        tags: [],
        keywords: [],
        topics: [],
        faqs: []
    });

    const canAnalyze = title.length >= 5 || content.length >= 50;

    // Load providers and user usage on component mount
    useEffect(() => {
        const loadAnalysisOptions = async () => {
            setIsLoadingOptions(true);
            try {
                const response = await fetch('/admin/blog/analysis/options', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                    },
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('Non-JSON response received from options:', textResponse.substring(0, 500));
                    return; // Skip error, just don't load providers
                }
                
                const result = await response.json();
                
                if (result.success) {
                    setProviders(result.data.providers || []);
                    setUserUsage(result.data.user_usage || null);
                }
            } catch (error) {
                console.error('Failed to load analysis options:', error);
            } finally {
                setIsLoadingOptions(false);
            }
        };

        loadAnalysisOptions();
    }, []);

    // Update cost estimate when provider or content changes
    useEffect(() => {
        const updateCostEstimate = async () => {
            if (!canAnalyze || selectedProvider === 'basic') {
                setEstimatedCost(0);
                return;
            }

            try {
                const response = await fetch('/admin/blog/analysis/cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken(),
                    },
                    body: JSON.stringify({
                        title: title.trim(),
                        content: content.trim(),
                        provider: selectedProvider
                    })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Non-JSON response received from cost estimation');
                    return; // Skip error, just don't update cost
                }
                
                const result = await response.json();
                
                if (result.success) {
                    setEstimatedCost(result.data.estimated_cost || 0);
                }
            } catch (error) {
                console.error('Failed to estimate cost:', error);
                setEstimatedCost(0);
            }
        };

        const timeoutId = setTimeout(updateCostEstimate, 500);
        return () => clearTimeout(timeoutId);
    }, [selectedProvider, title, content, canAnalyze]);

    const analyzeContent = useCallback(async () => {
        console.log('🔍 analyzeContent called with:', {
            canAnalyze,
            disabled,
            selectedProvider,
            titleLength: title.length,
            contentLength: content.length
        });

        if (!canAnalyze || disabled) {
            console.log('❌ Analysis skipped: canAnalyze =', canAnalyze, 'disabled =', disabled);
            return;
        }

        // Show confirmation for AI analysis to prevent accidental credit usage
        if (selectedProvider !== 'basic') {
            const confirmed = window.confirm(
                `Confirm AI Analysis?\n\n` +
                `This will use Claude API and cost approximately $${estimatedCost.toFixed(3)}.\n\n` +
                `Your current usage: ${userUsage?.analyses_used || 0}/${userUsage?.analyses_limit || 50} analyses this month\n\n` +
                `Click OK to proceed with AI analysis, or Cancel to use free basic analysis instead.`
            );
            
            if (!confirmed) {
                console.log('❌ User cancelled AI analysis');
                return; // User cancelled
            }
        }

        console.log('📊 Starting analysis with provider:', selectedProvider);
        setIsAnalyzing(true);
        setError(null);

        try {
            // Use AJAX endpoint with proper CSRF handling
            const endpoint = selectedProvider === 'basic' ? '/admin/blog/analysis/content' : '/admin/blog/analysis/ai';
            
            const requestBody = selectedProvider === 'basic' 
                ? {
                    title: title.trim(),
                    content: content.trim()
                  }
                : {
                    title: title.trim(),
                    content: content.trim(),
                    provider: selectedProvider
                  };

            console.log('🌐 Making request to:', endpoint, 'with body:', requestBody);

            const finalToken = getCSRFToken();
            console.log('🔐 CSRF Token retrieved:', finalToken ? 'Yes' : 'No');
            console.log('🔐 Token preview:', finalToken ? finalToken.substring(0, 10) + '...' : 'none');

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': finalToken,
                    'X-Requested-With': 'XMLHttpRequest', // Important for Laravel AJAX detection
                },
                body: JSON.stringify(requestBody)
            });

            console.log('📡 Response status:', response.status, 'OK:', response.ok);

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Non-JSON response received:', textResponse.substring(0, 500));
                throw new Error('Server returned HTML instead of JSON. Please check authentication and permissions.');
            }

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Analysis failed');
            }

            if (result.success) {
                console.log('Analysis result received:', result.data.analysis);
                setAnalysis(result.data.analysis);
                setError(null);
            } else {
                console.error('Analysis failed:', result);
                throw new Error(result.message || 'Analysis failed');
            }

        } catch (err) {
            console.error('Content analysis error:', err);
            setError(err instanceof Error ? err.message : 'Analysis failed. Please try again.');
            setAnalysis(null);
        } finally {
            setIsAnalyzing(false);
        }
    }, [title, content, canAnalyze, disabled, selectedProvider]);

    // Disable auto-analysis for now - make it fully manual for testing
    // useEffect(() => {
    //     if (!canAnalyze || selectedProvider !== 'basic') return;

    //     const timeoutId = setTimeout(() => {
    //         analyzeContent();
    //     }, 2000); // Debounce by 2 seconds

    //     return () => clearTimeout(timeoutId);
    // }, [title, content, analyzeContent, canAnalyze, selectedProvider]);

    const handleSuggestionToggle = (type: 'tags' | 'keywords' | 'topics', suggestion: any) => {
        setSelectedSuggestions(prev => {
            const updated = { ...prev };
            
            if (type === 'tags') {
                const exists = updated.tags.find(t => t.name === suggestion.name);
                if (exists) {
                    updated.tags = updated.tags.filter(t => t.name !== suggestion.name);
                } else {
                    updated.tags = [...updated.tags, suggestion];
                }
                onTagsSelected?.(updated.tags);
            } else if (type === 'keywords') {
                const exists = updated.keywords.includes(suggestion.name);
                if (exists) {
                    updated.keywords = updated.keywords.filter(k => k !== suggestion.name);
                } else {
                    updated.keywords = [...updated.keywords, suggestion.name];
                }
                onKeywordsSelected?.(updated.keywords);
            } else if (type === 'topics') {
                const exists = updated.topics.includes(suggestion.name);
                if (exists) {
                    updated.topics = updated.topics.filter(t => t !== suggestion.name);
                } else {
                    updated.topics = [...updated.topics, suggestion.name];
                }
                onTopicsSelected?.(updated.topics);
            }
            
            return updated;
        });
    };

    const handleFAQToggle = (faq: {question: string; answer: string}) => {
        setSelectedSuggestions(prev => {
            const updated = { ...prev };
            const exists = updated.faqs.find(f => f.question === faq.question);
            
            if (exists) {
                updated.faqs = updated.faqs.filter(f => f.question !== faq.question);
            } else {
                updated.faqs = [...updated.faqs, faq];
            }
            
            onFAQsSelected?.(updated.faqs);
            return updated;
        });
    };

    const getConfidenceColor = (confidence: number): string => {
        if (confidence >= 80) return 'text-green-600 dark:text-green-400';
        if (confidence >= 60) return 'text-yellow-600 dark:text-yellow-400';
        return 'text-red-600 dark:text-red-400';
    };

    const getConfidenceBadgeVariant = (confidence: number): 'default' | 'secondary' | 'destructive' => {
        if (confidence >= 80) return 'default';
        if (confidence >= 60) return 'secondary';
        return 'destructive';
    };

    if (!canAnalyze) {
        return (
            <Card className={className}>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Sparkles className="h-5 w-5" />
                        AI Content Analysis
                    </CardTitle>
                    <CardDescription>
                        Add a title (5+ characters) or content (50+ characters) to get AI-powered suggestions for tags, keywords, topics, and FAQs.
                    </CardDescription>
                </CardHeader>
            </Card>
        );
    }

    return (
        <Card className={className}>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2">
                            <Sparkles className="h-5 w-5" />
                            AI Content Analysis
                        </CardTitle>
                        <CardDescription>
                            Get AI-powered suggestions to optimize your content for search engines and answer engines.
                        </CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                        {selectedProvider !== 'basic' && estimatedCost > 0 && (
                            <div className="text-xs text-muted-foreground flex items-center gap-1">
                                <DollarSign className="h-3 w-3" />
                                ~${estimatedCost.toFixed(3)}
                            </div>
                        )}
                        <Button
                            variant={selectedProvider === 'basic' ? "outline" : "default"}
                            size="sm"
                            onClick={() => {
                                console.log('🔘 Analyze button clicked!');
                                analyzeContent();
                            }}
                            disabled={isAnalyzing || disabled || !canAnalyze}
                            className={cn(
                                "flex items-center gap-2",
                                selectedProvider !== 'basic' && "bg-yellow-600 hover:bg-yellow-700 text-white"
                            )}
                        >
                            <RefreshCw className={cn("h-4 w-4", isAnalyzing && "animate-spin")} />
                            {isAnalyzing ? 
                                (selectedProvider === 'basic' ? 'Analyzing...' : 'Analyzing with AI...') : 
                                (selectedProvider === 'basic' ? 'Analyze (Free)' : `Analyze with AI (~$${estimatedCost.toFixed(3)})`)
                            }
                        </Button>
                    </div>
                </div>
                
                {/* Cost Warning for AI Analysis */}
                {selectedProvider !== 'basic' && (
                    <Alert className="mt-4 border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20">
                        <Zap className="h-4 w-4 text-yellow-600" />
                        <AlertDescription className="text-yellow-800 dark:text-yellow-200">
                            <strong>AI Analysis Selected:</strong> This will consume Claude API credits (~${estimatedCost.toFixed(3)} per analysis). 
                            Only click "Analyze with AI" when you're ready to spend credits.
                        </AlertDescription>
                    </Alert>
                )}
                
                {/* Provider Selection */}
                {!isLoadingOptions && providers.length > 0 && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Analysis Provider:</label>
                            <Select value={selectedProvider} onValueChange={setSelectedProvider}>
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Select analysis provider" />
                                </SelectTrigger>
                                <SelectContent>
                                    {providers.map((provider) => (
                                        <SelectItem key={provider.key} value={provider.key}>
                                            <div className="flex items-center justify-between w-full">
                                                <div className="flex items-center gap-2">
                                                    {provider.key === 'basic' ? (
                                                        <Zap className="h-4 w-4 text-blue-500" />
                                                    ) : (
                                                        <Star className="h-4 w-4 text-yellow-500" />
                                                    )}
                                                    <span>{provider.name}</span>
                                                </div>
                                                <div className="flex items-center gap-2 ml-2">
                                                    <span className="text-xs text-muted-foreground">⭐{provider.quality_rating}/5</span>
                                                    {provider.cost > 0 && (
                                                        <span className="text-xs text-muted-foreground">${provider.cost.toFixed(3)}</span>
                                                    )}
                                                </div>
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {providers.find(p => p.key === selectedProvider) && (
                                <p className="text-xs text-muted-foreground">
                                    {providers.find(p => p.key === selectedProvider)?.description}
                                </p>
                            )}
                        </div>
                        
                        {/* Usage Display */}
                        {userUsage && selectedProvider !== 'basic' && (
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Monthly Usage:</label>
                                <div className="space-y-1">
                                    <div className="flex justify-between text-xs">
                                        <span>{userUsage.analyses_used}/{userUsage.analyses_limit} analyses</span>
                                        <span>${userUsage.cost_used.toFixed(2)}/${userUsage.cost_limit.toFixed(2)}</span>
                                    </div>
                                    <Progress value={userUsage.percentage_used} className="h-2" />
                                    <p className="text-xs text-muted-foreground">
                                        {userUsage.percentage_used < 90 ? 'Usage within limits' : 'Approaching usage limit'}
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                )}
                
                {analysis && (
                    <div className="flex items-center gap-4 mt-2">
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-muted-foreground">Confidence:</span>
                            <Badge variant={getConfidenceBadgeVariant(analysis.confidence)}>
                                {analysis.confidence}%
                            </Badge>
                        </div>
                        <Separator orientation="vertical" className="h-4" />
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Clock className="h-3 w-3" />
                            {analysis.suggestions.reading_time} min read
                        </div>
                        <Separator orientation="vertical" className="h-4" />
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <TrendingUp className="h-3 w-3" />
                            {analysis.suggestions.content_type.replace('_', ' ')}
                        </div>
                        {analysis.metadata.provider && (
                            <>
                                <Separator orientation="vertical" className="h-4" />
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    {analysis.metadata.provider === 'basic' ? (
                                        <Zap className="h-3 w-3 text-blue-500" />
                                    ) : (
                                        <Star className="h-3 w-3 text-yellow-500" />
                                    )}
                                    {analysis.metadata.provider === 'claude' ? 'Claude AI' : 
                                     analysis.metadata.provider === 'basic' ? 'Basic Analysis' : 
                                     analysis.metadata.provider}
                                </div>
                            </>
                        )}
                        {analysis.metadata.cost && analysis.metadata.cost > 0 && (
                            <>
                                <Separator orientation="vertical" className="h-4" />
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <DollarSign className="h-3 w-3" />
                                    ${analysis.metadata.cost.toFixed(3)}
                                </div>
                            </>
                        )}
                    </div>
                )}
            </CardHeader>

            <CardContent>
                {error && (
                    <Alert variant="destructive" className="mb-4">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {isAnalyzing && (
                    <div className="flex items-center justify-center py-8">
                        <div className="text-center">
                            <RefreshCw className="h-8 w-8 animate-spin mx-auto mb-2 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">Analyzing your content...</p>
                        </div>
                    </div>
                )}

                {analysis && !isAnalyzing && (
                    <div className="space-y-4">
                        <Tabs value={selectedTab} onValueChange={setSelectedTab}>
                            <TabsList className="grid w-full grid-cols-4">
                                <TabsTrigger value="tags" className="flex items-center gap-1">
                                    <Hash className="h-3 w-3" />
                                    Tags ({analysis.suggestions.tags.length})
                                </TabsTrigger>
                                <TabsTrigger value="keywords" className="flex items-center gap-1">
                                    <Target className="h-3 w-3" />
                                    Keywords ({analysis.suggestions.keywords.length})
                                </TabsTrigger>
                                <TabsTrigger value="topics" className="flex items-center gap-1">
                                    <Lightbulb className="h-3 w-3" />
                                    Topics ({analysis.suggestions.topics.length})
                                </TabsTrigger>
                                <TabsTrigger value="faqs" className="flex items-center gap-1">
                                    <MessageSquare className="h-3 w-3" />
                                    FAQs ({analysis.suggestions.faqs.length})
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="tags" className="space-y-3">
                                <div className="text-sm text-muted-foreground mb-2">
                                    Click to select tags that best describe your content:
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {analysis.suggestions.tags.map((tag, index) => {
                                        const isSelected = selectedSuggestions.tags.find(t => t.name === tag.name);
                                        return (
                                            <Badge
                                                key={index}
                                                variant={isSelected ? "default" : "outline"}
                                                className="cursor-pointer hover:bg-primary/20 flex items-center gap-1"
                                                onClick={() => handleSuggestionToggle('tags', tag)}
                                            >
                                                {isSelected && <CheckCircle className="h-3 w-3" />}
                                                #{tag.name}
                                                <span className={cn("text-xs", getConfidenceColor(tag.confidence))}>
                                                    {tag.confidence}%
                                                </span>
                                                {tag.exists && (
                                                    <span className="text-xs text-blue-500">existing</span>
                                                )}
                                            </Badge>
                                        );
                                    })}
                                </div>
                            </TabsContent>

                            <TabsContent value="keywords" className="space-y-3">
                                <div className="text-sm text-muted-foreground mb-2">
                                    Select keywords to improve your SEO:
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {analysis.suggestions.keywords.map((keyword, index) => {
                                        const isSelected = selectedSuggestions.keywords.includes(keyword.name);
                                        return (
                                            <Badge
                                                key={index}
                                                variant={isSelected ? "default" : "outline"}
                                                className="cursor-pointer hover:bg-primary/20 flex items-center gap-1"
                                                onClick={() => handleSuggestionToggle('keywords', keyword)}
                                            >
                                                {isSelected && <CheckCircle className="h-3 w-3" />}
                                                {keyword.name}
                                                <span className={cn("text-xs", getConfidenceColor(keyword.confidence))}>
                                                    {keyword.confidence}%
                                                </span>
                                            </Badge>
                                        );
                                    })}
                                </div>
                            </TabsContent>

                            <TabsContent value="topics" className="space-y-3">
                                <div className="text-sm text-muted-foreground mb-2">
                                    Choose topics that align with your content:
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {analysis.suggestions.topics.map((topic, index) => {
                                        const isSelected = selectedSuggestions.topics.includes(topic.name);
                                        return (
                                            <Badge
                                                key={index}
                                                variant={isSelected ? "default" : "outline"}
                                                className="cursor-pointer hover:bg-primary/20 flex items-center gap-1"
                                                onClick={() => handleSuggestionToggle('topics', topic)}
                                            >
                                                {isSelected && <CheckCircle className="h-3 w-3" />}
                                                {topic.name}
                                                <span className={cn("text-xs", getConfidenceColor(topic.confidence))}>
                                                    {topic.confidence}%
                                                </span>
                                            </Badge>
                                        );
                                    })}
                                </div>
                            </TabsContent>

                            <TabsContent value="faqs" className="space-y-3">
                                <div className="text-sm text-muted-foreground mb-2">
                                    Select relevant FAQs to enhance your content:
                                </div>
                                <div className="space-y-2">
                                    {analysis.suggestions.faqs.map((faq, index) => {
                                        const isSelected = selectedSuggestions.faqs.find(f => f.question === faq.question);
                                        return (
                                            <div
                                                key={index}
                                                className={cn(
                                                    "p-3 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors",
                                                    isSelected && "bg-primary/5 border-primary"
                                                )}
                                                onClick={() => handleFAQToggle({
                                                    question: faq.question,
                                                    answer: faq.answer
                                                })}
                                            >
                                                <div className="flex items-start justify-between gap-2">
                                                    <div className="flex-1">
                                                        <div className="flex items-center gap-2 mb-1">
                                                            {isSelected && <CheckCircle className="h-4 w-4 text-primary" />}
                                                            <HelpCircle className="h-4 w-4 text-muted-foreground" />
                                                            <span className="font-medium text-sm">{faq.question}</span>
                                                        </div>
                                                        <p className="text-xs text-muted-foreground pl-6">
                                                            {faq.answer.substring(0, 120)}
                                                            {faq.answer.length > 120 && '...'}
                                                        </p>
                                                    </div>
                                                    <div className="flex flex-col items-end gap-1">
                                                        <Badge variant="outline" className="text-xs">
                                                            {faq.confidence}%
                                                        </Badge>
                                                        <span className="text-xs text-muted-foreground">
                                                            {faq.type}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </TabsContent>
                        </Tabs>

                        {/* Summary of selections */}
                        {(selectedSuggestions.tags.length > 0 || 
                          selectedSuggestions.keywords.length > 0 || 
                          selectedSuggestions.topics.length > 0 || 
                          selectedSuggestions.faqs.length > 0) && (
                            <Alert className="mt-4">
                                <CheckCircle className="h-4 w-4" />
                                <AlertDescription>
                                    Selected: {selectedSuggestions.tags.length} tags, {selectedSuggestions.keywords.length} keywords, 
                                    {selectedSuggestions.topics.length} topics, and {selectedSuggestions.faqs.length} FAQs. 
                                    These will be automatically applied to your post.
                                </AlertDescription>
                            </Alert>
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}