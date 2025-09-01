import React, { useState, useEffect, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Sparkles, 
    Target, 
    RefreshCw, 
    CheckCircle, 
    AlertCircle,
    TrendingUp,
    Hash,
    MessageSquare,
    Zap,
    DollarSign,
    Star,
    Plus,
    X,
    Edit,
    Save,
    Wand2,
    Eye,
    EyeOff
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';

// Types
interface SEOKeyword {
    term: string;
    type: 'primary' | 'secondary' | 'long_tail' | 'question';
    confidence: number;
    search_intent: 'informational' | 'navigational' | 'commercial' | 'transactional';
    reason: string;
    search_volume?: 'high' | 'medium' | 'low';
    source?: 'ai' | 'manual';
}

interface EnhancedTag {
    tag_id?: number | null;
    name: string;
    seo_weight: number;
    ai_suggested: boolean;
    confidence: number;
    source: 'ai' | 'manual';
    reason?: string;
    requires_creation?: boolean;
}

interface OptimizationData {
    schema_type: string;
    content_type: string;
    ai_confidence?: number;
    quality_score?: number;
    seo_score?: number;
    readability_score?: number;
    keyword_density?: number;
    improvements?: string[];
    optimization_timestamp: string;
    method: 'ai_generated' | 'manual_only' | 'ai_with_manual_override';
    schema_requirements?: string[];
}

interface UnifiedSEOData {
    seo_keywords: SEOKeyword[];
    enhanced_tags: EnhancedTag[];
    optimization_data: OptimizationData;
    ai_optimized_at?: string | null;
    ai_model_used?: string | null;
}

interface Props {
    title: string;
    content: string;
    schemaType: string;
    initialData?: Partial<UnifiedSEOData>;
    availableTags: Array<{ id: number; name: string; color?: string }>;
    onOptimizationUpdate: (data: UnifiedSEOData) => void;
    disabled?: boolean;
}

const getCSRFToken = (): string => {
    const inertiaPage = (window as any)?.page;
    if (inertiaPage?.props?.csrfToken) return inertiaPage.props.csrfToken;
    
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (metaToken) return metaToken;
    
    return '';
};

export default function UnifiedSEOOptimizer({ 
    title, 
    content, 
    schemaType,
    initialData,
    availableTags,
    onOptimizationUpdate,
    disabled = false 
}: Props) {
    const [isAnalyzing, setIsAnalyzing] = useState(false);
    const [analysisError, setAnalysisError] = useState<string | null>(null);
    const [currentData, setCurrentData] = useState<UnifiedSEOData>({
        seo_keywords: initialData?.seo_keywords || [],
        enhanced_tags: initialData?.enhanced_tags || [],
        optimization_data: initialData?.optimization_data || {
            schema_type: schemaType,
            content_type: 'blog_post',
            optimization_timestamp: new Date().toISOString(),
            method: 'manual_only',
            schema_requirements: []
        },
        ai_optimized_at: initialData?.ai_optimized_at || null,
        ai_model_used: initialData?.ai_model_used || null
    });

    // Manual editing states
    const [isEditingKeywords, setIsEditingKeywords] = useState(false);
    const [isEditingTags, setIsEditingTags] = useState(false);
    const [newKeyword, setNewKeyword] = useState<Partial<SEOKeyword>>({
        term: '',
        type: 'primary',
        confidence: 85,
        search_intent: 'informational',
        reason: 'Manually added',
        source: 'manual'
    });

    // Update schema type when it changes
    useEffect(() => {
        if (schemaType !== currentData.optimization_data.schema_type) {
            setCurrentData(prev => ({
                ...prev,
                optimization_data: {
                    ...prev.optimization_data,
                    schema_type: schemaType
                }
            }));
        }
    }, [schemaType]);

    // Notify parent of changes
    useEffect(() => {
        onOptimizationUpdate(currentData);
    }, [currentData, onOptimizationUpdate]);

    const runAIOptimization = useCallback(async () => {
        if (!title.trim() && !content.trim()) {
            setAnalysisError('Please provide title and content for AI analysis');
            return;
        }

        setIsAnalyzing(true);
        setAnalysisError(null);

        try {
            const response = await fetch('/admin/blog/analysis/unified', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: title.trim(),
                    content: content.trim(),
                    schema_type: schemaType,
                    use_ai: true,
                    manual_overrides: {
                        // Preserve any manually added keywords/tags
                        seo_keywords: currentData.seo_keywords.filter(k => k.source === 'manual'),
                        enhanced_tags: currentData.enhanced_tags.filter(t => t.source === 'manual')
                    }
                })
            });

            if (!response.ok) {
                throw new Error(`Analysis failed: ${response.status}`);
            }

            const result = await response.json();
            
            setCurrentData(result.optimization_data);

        } catch (error) {
            console.error('AI optimization failed:', error);
            setAnalysisError(error instanceof Error ? error.message : 'Analysis failed');
        } finally {
            setIsAnalyzing(false);
        }
    }, [title, content, schemaType, currentData.seo_keywords, currentData.enhanced_tags]);

    const addManualKeyword = () => {
        if (!newKeyword.term?.trim()) return;

        const keyword: SEOKeyword = {
            term: newKeyword.term.trim(),
            type: newKeyword.type || 'primary',
            confidence: newKeyword.confidence || 85,
            search_intent: newKeyword.search_intent || 'informational', 
            reason: newKeyword.reason || 'Manually added',
            source: 'manual'
        };

        setCurrentData(prev => ({
            ...prev,
            seo_keywords: [...prev.seo_keywords, keyword],
            optimization_data: {
                ...prev.optimization_data,
                method: prev.optimization_data.method === 'ai_generated' ? 'ai_with_manual_override' : 'manual_only'
            }
        }));

        setNewKeyword({
            term: '',
            type: 'primary',
            confidence: 85,
            search_intent: 'informational',
            reason: 'Manually added',
            source: 'manual'
        });
    };

    const removeKeyword = (index: number) => {
        setCurrentData(prev => ({
            ...prev,
            seo_keywords: prev.seo_keywords.filter((_, i) => i !== index)
        }));
    };

    const toggleTagSelection = (tag: { id: number; name: string; color?: string }) => {
        const existingIndex = currentData.enhanced_tags.findIndex(t => t.tag_id === tag.id);
        
        if (existingIndex >= 0) {
            // Remove tag
            setCurrentData(prev => ({
                ...prev,
                enhanced_tags: prev.enhanced_tags.filter((_, i) => i !== existingIndex)
            }));
        } else {
            // Add tag
            const enhancedTag: EnhancedTag = {
                tag_id: tag.id,
                name: tag.name,
                seo_weight: 0.7,
                ai_suggested: false,
                confidence: 100,
                source: 'manual'
            };

            setCurrentData(prev => ({
                ...prev,
                enhanced_tags: [...prev.enhanced_tags, enhancedTag],
                optimization_data: {
                    ...prev.optimization_data,
                    method: prev.optimization_data.method === 'ai_generated' ? 'ai_with_manual_override' : 'manual_only'
                }
            }));
        }
    };

    const getKeywordTypeColor = (type: string) => {
        switch (type) {
            case 'primary': return 'bg-blue-100 text-blue-800';
            case 'secondary': return 'bg-green-100 text-green-800';
            case 'long_tail': return 'bg-purple-100 text-purple-800';
            case 'question': return 'bg-orange-100 text-orange-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getConfidenceColor = (confidence: number) => {
        if (confidence >= 90) return 'text-green-600';
        if (confidence >= 75) return 'text-yellow-600';
        return 'text-red-600';
    };

    return (
        <Card className="w-full">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2">
                            <Sparkles className="h-5 w-5" />
                            Unified SEO Optimizer
                        </CardTitle>
                        <CardDescription>
                            AI-powered SEO optimization with manual override capabilities
                        </CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            onClick={runAIOptimization}
                            disabled={isAnalyzing || disabled || (!title.trim() && !content.trim())}
                            size="sm"
                            className="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700"
                        >
                            {isAnalyzing ? (
                                <>
                                    <RefreshCw className="h-4 w-4 mr-2 animate-spin" />
                                    Analyzing...
                                </>
                            ) : (
                                <>
                                    <Wand2 className="h-4 w-4 mr-2" />
                                    AI Optimize
                                </>
                            )}
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent className="space-y-6">
                {analysisError && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{analysisError}</AlertDescription>
                    </Alert>
                )}

                {/* Optimization Status */}
                {currentData.ai_optimized_at && (
                    <Alert>
                        <CheckCircle className="h-4 w-4" />
                        <AlertDescription>
                            Last optimized by {currentData.ai_model_used} on{' '}
                            {new Date(currentData.ai_optimized_at).toLocaleDateString()}
                        </AlertDescription>
                    </Alert>
                )}

                <Tabs defaultValue="keywords" className="w-full">
                    <TabsList className="grid w-full grid-cols-3">
                        <TabsTrigger value="keywords">
                            <Hash className="h-4 w-4 mr-2" />
                            Keywords ({currentData.seo_keywords.length})
                        </TabsTrigger>
                        <TabsTrigger value="tags">
                            <Target className="h-4 w-4 mr-2" />
                            Tags ({currentData.enhanced_tags.length})
                        </TabsTrigger>
                        <TabsTrigger value="analysis">
                            <TrendingUp className="h-4 w-4 mr-2" />
                            Analysis
                        </TabsTrigger>
                    </TabsList>

                    {/* Keywords Tab */}
                    <TabsContent value="keywords" className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-sm font-medium">SEO Keywords</h3>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setIsEditingKeywords(!isEditingKeywords)}
                            >
                                {isEditingKeywords ? <EyeOff className="h-4 w-4" /> : <Edit className="h-4 w-4" />}
                                {isEditingKeywords ? 'View' : 'Edit'}
                            </Button>
                        </div>

                        {/* Add New Keyword */}
                        {isEditingKeywords && (
                            <Card className="p-4">
                                <div className="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <Label htmlFor="keyword-term">Keyword</Label>
                                        <Input
                                            id="keyword-term"
                                            value={newKeyword.term}
                                            onChange={(e) => setNewKeyword(prev => ({ ...prev, term: e.target.value }))}
                                            placeholder="Enter keyword phrase..."
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="keyword-type">Type</Label>
                                        <Select 
                                            value={newKeyword.type} 
                                            onValueChange={(value) => setNewKeyword(prev => ({ ...prev, type: value as any }))}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="primary">Primary</SelectItem>
                                                <SelectItem value="secondary">Secondary</SelectItem>
                                                <SelectItem value="long_tail">Long-tail</SelectItem>
                                                <SelectItem value="question">Question</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                                <Button onClick={addManualKeyword} size="sm" disabled={!newKeyword.term?.trim()}>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Add Keyword
                                </Button>
                            </Card>
                        )}

                        {/* Keywords List */}
                        <div className="space-y-2">
                            {currentData.seo_keywords.map((keyword, index) => (
                                <div
                                    key={index}
                                    className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <div className="flex items-center gap-3">
                                        <Badge className={getKeywordTypeColor(keyword.type)}>
                                            {keyword.type}
                                        </Badge>
                                        <span className="font-medium">{keyword.term}</span>
                                        <span className={cn('text-sm font-medium', getConfidenceColor(keyword.confidence))}>
                                            {keyword.confidence}%
                                        </span>
                                        {keyword.source === 'ai' && <Sparkles className="h-4 w-4 text-blue-500" />}
                                    </div>
                                    {isEditingKeywords && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeKeyword(index)}
                                        >
                                            <X className="h-4 w-4" />
                                        </Button>
                                    )}
                                </div>
                            ))}
                            {currentData.seo_keywords.length === 0 && (
                                <p className="text-center text-gray-500 py-8">
                                    No keywords yet. Click "AI Optimize" or add manually.
                                </p>
                            )}
                        </div>
                    </TabsContent>

                    {/* Tags Tab */}
                    <TabsContent value="tags" className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-sm font-medium">Enhanced Tags</h3>
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => setIsEditingTags(!isEditingTags)}
                            >
                                {isEditingTags ? <EyeOff className="h-4 w-4" /> : <Edit className="h-4 w-4" />}
                                {isEditingTags ? 'View' : 'Edit'}
                            </Button>
                        </div>

                        {/* Tag Selection */}
                        {isEditingTags && (
                            <Card className="p-4">
                                <Label className="text-sm font-medium mb-3 block">Available Tags</Label>
                                <div className="flex flex-wrap gap-2">
                                    {availableTags.map((tag) => {
                                        const isSelected = currentData.enhanced_tags.some(t => t.tag_id === tag.id);
                                        return (
                                            <Badge
                                                key={tag.id}
                                                variant={isSelected ? "default" : "outline"}
                                                className={cn(
                                                    "cursor-pointer hover:bg-opacity-80",
                                                    isSelected && "bg-blue-600"
                                                )}
                                                onClick={() => toggleTagSelection(tag)}
                                            >
                                                {tag.name}
                                                {isSelected && <CheckCircle className="h-3 w-3 ml-1" />}
                                            </Badge>
                                        );
                                    })}
                                </div>
                            </Card>
                        )}

                        {/* Selected Tags */}
                        <div className="space-y-2">
                            {currentData.enhanced_tags.map((tag, index) => (
                                <div
                                    key={index}
                                    className="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <div className="flex items-center gap-3">
                                        <Badge variant="outline">{tag.name}</Badge>
                                        <div className="flex items-center gap-2 text-sm text-gray-600">
                                            <span>Weight: {Math.round(tag.seo_weight * 100)}%</span>
                                            <span className={getConfidenceColor(tag.confidence)}>
                                                {tag.confidence}%
                                            </span>
                                            {tag.ai_suggested && <Sparkles className="h-4 w-4 text-blue-500" />}
                                        </div>
                                    </div>
                                </div>
                            ))}
                            {currentData.enhanced_tags.length === 0 && (
                                <p className="text-center text-gray-500 py-8">
                                    No tags selected. Choose from available tags above.
                                </p>
                            )}
                        </div>
                    </TabsContent>

                    {/* Analysis Tab */}
                    <TabsContent value="analysis" className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <Card className="p-4">
                                <div className="flex items-center gap-2 mb-2">
                                    <Star className="h-4 w-4 text-yellow-500" />
                                    <span className="font-medium">Quality Score</span>
                                </div>
                                <div className="text-2xl font-bold">
                                    {currentData.optimization_data.quality_score || 'N/A'}
                                </div>
                                <Progress 
                                    value={currentData.optimization_data.quality_score || 0} 
                                    className="mt-2" 
                                />
                            </Card>

                            <Card className="p-4">
                                <div className="flex items-center gap-2 mb-2">
                                    <TrendingUp className="h-4 w-4 text-green-500" />
                                    <span className="font-medium">SEO Score</span>
                                </div>
                                <div className="text-2xl font-bold">
                                    {currentData.optimization_data.seo_score || 'N/A'}
                                </div>
                                <Progress 
                                    value={currentData.optimization_data.seo_score || 0} 
                                    className="mt-2" 
                                />
                            </Card>
                        </div>

                        {/* Optimization Method */}
                        <Card className="p-4">
                            <div className="flex items-center gap-2 mb-2">
                                <MessageSquare className="h-4 w-4" />
                                <span className="font-medium">Optimization Method</span>
                            </div>
                            <Badge variant="outline">
                                {currentData.optimization_data.method.replace(/_/g, ' ').toUpperCase()}
                            </Badge>
                        </Card>

                        {/* Improvements */}
                        {currentData.optimization_data.improvements && currentData.optimization_data.improvements.length > 0 && (
                            <Card className="p-4">
                                <div className="flex items-center gap-2 mb-3">
                                    <Zap className="h-4 w-4 text-purple-500" />
                                    <span className="font-medium">Suggested Improvements</span>
                                </div>
                                <ul className="space-y-2">
                                    {currentData.optimization_data.improvements.map((improvement, index) => (
                                        <li key={index} className="text-sm text-gray-600 flex items-start gap-2">
                                            <div className="w-1.5 h-1.5 bg-purple-500 rounded-full mt-2 flex-shrink-0" />
                                            {improvement}
                                        </li>
                                    ))}
                                </ul>
                            </Card>
                        )}
                    </TabsContent>
                </Tabs>
            </CardContent>
        </Card>
    );
}