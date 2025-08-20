import React, { useState, useRef, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Search, X, Plus, Target, AlertCircle, Lightbulb, TrendingUp } from 'lucide-react';

interface KeywordSuggestion {
    keyword: string;
    type: 'primary' | 'secondary' | 'long_tail';
    relevance?: number;
}

interface KeywordManagerProps {
    value: string[];
    onChange: (keywords: string[]) => void;
    maxKeywords?: number;
    disabled?: boolean;
    error?: string;
    suggestions?: KeywordSuggestion[];
    contentPreview?: string; // For context-aware suggestions
}

/**
 * KeywordManager Component
 * 
 * Provides an interface for managing SEO/AEO keywords that enhance content 
 * discoverability in traditional and AI-powered search engines.
 * 
 * Features:
 * - Add/remove keywords with validation
 * - Keyword type classification (primary, secondary, long-tail)
 * - Context-aware suggestions from content
 * - Duplicate prevention and formatting
 * - Real-time keyword analysis
 * 
 * AEO Integration:
 * - Enhances schema.org keyword properties
 * - Improves content semantic understanding
 * - Supports answer engine optimization
 */
export function KeywordManager({ 
    value = [], 
    onChange, 
    maxKeywords = 10, 
    disabled = false, 
    error,
    suggestions = [],
    contentPreview = ''
}: KeywordManagerProps) {
    const [inputValue, setInputValue] = useState('');
    const [showSuggestions, setShowSuggestions] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    // Default keyword suggestions categorized by type
    const defaultSuggestions: KeywordSuggestion[] = [
        // Primary keywords (broad, high-volume)
        { keyword: 'web development', type: 'primary' },
        { keyword: 'digital marketing', type: 'primary' },
        { keyword: 'artificial intelligence', type: 'primary' },
        { keyword: 'user experience', type: 'primary' },
        { keyword: 'content strategy', type: 'primary' },
        
        // Secondary keywords (specific, medium-volume)
        { keyword: 'react development', type: 'secondary' },
        { keyword: 'seo optimization', type: 'secondary' },
        { keyword: 'machine learning', type: 'secondary' },
        { keyword: 'responsive design', type: 'secondary' },
        { keyword: 'content management', type: 'secondary' },
        
        // Long-tail keywords (very specific, lower-volume but higher intent)
        { keyword: 'how to optimize website speed', type: 'long_tail' },
        { keyword: 'best practices for react hooks', type: 'long_tail' },
        { keyword: 'ai content generation tools', type: 'long_tail' },
        { keyword: 'mobile first design principles', type: 'long_tail' },
        { keyword: 'schema markup implementation guide', type: 'long_tail' }
    ];

    const allSuggestions = [...suggestions, ...defaultSuggestions];
    
    const filteredSuggestions = allSuggestions.filter(suggestion => 
        suggestion.keyword.toLowerCase().includes(inputValue.toLowerCase()) &&
        !value.includes(suggestion.keyword) &&
        inputValue.trim() !== ''
    ).slice(0, 8);

    const addKeyword = (keyword: string, type?: 'primary' | 'secondary' | 'long_tail') => {
        const trimmedKeyword = keyword.trim().toLowerCase();
        
        if (!trimmedKeyword) return;
        if (value.includes(trimmedKeyword)) return;
        if (value.length >= maxKeywords) return;
        
        onChange([...value, trimmedKeyword]);
        setInputValue('');
        setShowSuggestions(false);
    };

    const removeKeyword = (keywordToRemove: string) => {
        onChange(value.filter(keyword => keyword !== keywordToRemove));
    };

    const handleInputKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (inputValue.trim()) {
                addKeyword(inputValue);
            }
        } else if (e.key === 'Escape') {
            setShowSuggestions(false);
            inputRef.current?.blur();
        } else if (e.key === 'Backspace' && !inputValue && value.length > 0) {
            removeKeyword(value[value.length - 1]);
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newValue = e.target.value;
        setInputValue(newValue);
        setShowSuggestions(newValue.trim().length > 0);
    };

    const handleInputFocus = () => {
        if (inputValue.trim()) {
            setShowSuggestions(true);
        }
    };

    const handleInputBlur = () => {
        setTimeout(() => setShowSuggestions(false), 200);
    };

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (inputRef.current && !inputRef.current.contains(event.target as Node)) {
                setShowSuggestions(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const getKeywordTypeIcon = (type: 'primary' | 'secondary' | 'long_tail') => {
        switch (type) {
            case 'primary':
                return <Target className="h-3 w-3 text-red-500" />;
            case 'secondary':
                return <Search className="h-3 w-3 text-blue-500" />;
            case 'long_tail':
                return <TrendingUp className="h-3 w-3 text-green-500" />;
        }
    };

    const getKeywordTypeColor = (type: 'primary' | 'secondary' | 'long_tail') => {
        switch (type) {
            case 'primary':
                return 'bg-red-100 text-red-800 border-red-200';
            case 'secondary':
                return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'long_tail':
                return 'bg-green-100 text-green-800 border-green-200';
        }
    };

    const getKeywordTypeLabel = (type: 'primary' | 'secondary' | 'long_tail') => {
        switch (type) {
            case 'primary':
                return 'Primary';
            case 'secondary':
                return 'Secondary';
            case 'long_tail':
                return 'Long-tail';
        }
    };

    // Simple keyword analysis
    const analyzeKeywords = () => {
        const analysis = {
            primary: value.filter(k => k.split(' ').length <= 2).length,
            secondary: value.filter(k => k.split(' ').length === 3).length,
            long_tail: value.filter(k => k.split(' ').length > 3).length,
            total: value.length
        };
        return analysis;
    };

    const keywordAnalysis = analyzeKeywords();

    return (
        <Card className="w-full">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Search className="h-5 w-5 text-purple-600" />
                        <CardTitle>SEO Keywords</CardTitle>
                        <Badge variant="secondary">
                            {value.length}/{maxKeywords}
                        </Badge>
                    </div>
                </div>
                <CardDescription>
                    Add relevant keywords to improve search engine discoverability and help AI understand your content's focus. 
                    Include primary keywords, secondary terms, and long-tail phrases for comprehensive coverage.
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4">
                {error && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {/* Keyword Input */}
                <div className="space-y-2">
                    <Label htmlFor="keyword-input">Add Keywords</Label>
                    <div className="relative">
                        <Input
                            ref={inputRef}
                            id="keyword-input"
                            value={inputValue}
                            onChange={handleInputChange}
                            onKeyDown={handleInputKeyDown}
                            onFocus={handleInputFocus}
                            onBlur={handleInputBlur}
                            placeholder={value.length < maxKeywords ? "Type a keyword and press Enter..." : "Maximum keywords reached"}
                            disabled={disabled || value.length >= maxKeywords}
                            className="pr-10"
                        />
                        {inputValue && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                className="absolute right-1 top-1 h-8 w-8 p-0"
                                onClick={() => addKeyword(inputValue)}
                                disabled={disabled || value.length >= maxKeywords}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        )}

                        {/* Suggestions Dropdown */}
                        {showSuggestions && filteredSuggestions.length > 0 && (
                            <div className="absolute z-10 w-full mt-1 bg-white border border-border rounded-md shadow-lg max-h-48 overflow-auto">
                                {filteredSuggestions.map((suggestion, index) => (
                                    <button
                                        key={suggestion.keyword}
                                        type="button"
                                        className="w-full px-3 py-2 text-left hover:bg-muted text-sm border-none bg-transparent cursor-pointer"
                                        onClick={() => addKeyword(suggestion.keyword, suggestion.type)}
                                        disabled={disabled}
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center space-x-2">
                                                {getKeywordTypeIcon(suggestion.type)}
                                                <span>{suggestion.keyword}</span>
                                            </div>
                                            <Badge variant="outline" className={`text-xs ${getKeywordTypeColor(suggestion.type)}`}>
                                                {getKeywordTypeLabel(suggestion.type)}
                                            </Badge>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                    <p className="text-xs text-muted-foreground">
                        Use specific, relevant terms that describe your content
                    </p>
                </div>

                {/* Selected Keywords */}
                {value.length > 0 && (
                    <div className="space-y-2">
                        <Label>Selected Keywords</Label>
                        <div className="flex flex-wrap gap-2">
                            {value.map((keyword) => {
                                // Simple type detection based on word count
                                const wordCount = keyword.split(' ').length;
                                const type = wordCount <= 2 ? 'primary' : wordCount === 3 ? 'secondary' : 'long_tail';
                                
                                return (
                                    <Badge
                                        key={keyword}
                                        variant="secondary"
                                        className={`${getKeywordTypeColor(type)} flex items-center space-x-1 pr-1`}
                                    >
                                        {getKeywordTypeIcon(type)}
                                        <span>{keyword}</span>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className="h-4 w-4 p-0 hover:bg-transparent"
                                            onClick={() => removeKeyword(keyword)}
                                            disabled={disabled}
                                        >
                                            <X className="h-3 w-3" />
                                        </Button>
                                    </Badge>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* Keyword Analysis */}
                {value.length > 0 && (
                    <div className="space-y-2">
                        <Label>Keyword Distribution</Label>
                        <div className="grid grid-cols-3 gap-2 text-sm">
                            <div className="flex items-center space-x-2 p-2 bg-red-50 rounded border border-red-200">
                                <Target className="h-4 w-4 text-red-500" />
                                <span className="text-red-700">Primary: {keywordAnalysis.primary}</span>
                            </div>
                            <div className="flex items-center space-x-2 p-2 bg-blue-50 rounded border border-blue-200">
                                <Search className="h-4 w-4 text-blue-500" />
                                <span className="text-blue-700">Secondary: {keywordAnalysis.secondary}</span>
                            </div>
                            <div className="flex items-center space-x-2 p-2 bg-green-50 rounded border border-green-200">
                                <TrendingUp className="h-4 w-4 text-green-500" />
                                <span className="text-green-700">Long-tail: {keywordAnalysis.long_tail}</span>
                            </div>
                        </div>
                    </div>
                )}

                {/* Suggested Keywords */}
                {value.length < maxKeywords && inputValue === '' && (
                    <div className="space-y-2">
                        <Label className="flex items-center space-x-1">
                            <Lightbulb className="h-4 w-4 text-yellow-500" />
                            <span>Suggested Keywords</span>
                        </Label>
                        <div className="space-y-2">
                            {['primary', 'secondary', 'long_tail'].map(type => {
                                const typeKeywords = allSuggestions
                                    .filter(s => s.type === type && !value.includes(s.keyword))
                                    .slice(0, 3);
                                
                                if (typeKeywords.length === 0) return null;
                                
                                return (
                                    <div key={type} className="space-y-1">
                                        <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                            {getKeywordTypeIcon(type as any)}
                                            <span className="font-medium">{getKeywordTypeLabel(type as any)} Keywords</span>
                                        </div>
                                        <div className="flex flex-wrap gap-1">
                                            {typeKeywords.map((suggestion) => (
                                                <Button
                                                    key={suggestion.keyword}
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    className="h-6 text-xs"
                                                    onClick={() => addKeyword(suggestion.keyword, suggestion.type)}
                                                    disabled={disabled || value.length >= maxKeywords}
                                                >
                                                    <Plus className="h-3 w-3 mr-1" />
                                                    {suggestion.keyword}
                                                </Button>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* AEO Benefits Info */}
                {value.length > 0 && (
                    <Alert>
                        <Search className="h-4 w-4" />
                        <AlertDescription>
                            <strong>AEO Benefits:</strong> {value.length} keyword{value.length > 1 ? 's' : ''} will be added to your content's 
                            schema markup, helping both traditional search engines and AI systems understand your content's focus and relevance.
                        </AlertDescription>
                    </Alert>
                )}

                {value.length === maxKeywords && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>
                            Maximum {maxKeywords} keywords reached. Remove a keyword to add a new one.
                        </AlertDescription>
                    </Alert>
                )}
            </CardContent>
        </Card>
    );
}

export default KeywordManager;