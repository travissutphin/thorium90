import React, { useState, useRef, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Tag, X, Plus, Hash, AlertCircle, Lightbulb } from 'lucide-react';

interface TopicSelectorProps {
    value: string[];
    onChange: (topics: string[]) => void;
    maxTopics?: number;
    disabled?: boolean;
    error?: string;
    suggestions?: string[];
}

/**
 * TopicSelector Component
 * 
 * Provides an interface for managing content topics/categories for AEO optimization.
 * Topics help AI engines understand content context and generate proper breadcrumb
 * navigation and entity relationships.
 * 
 * Features:
 * - Add/remove topics with validation
 * - Topic suggestions and auto-completion
 * - Category hierarchy support
 * - Duplicate prevention
 * - Real-time topic count tracking
 * 
 * AEO Integration:
 * - Generates breadcrumb schema
 * - Creates topic entity relationships
 * - Enhances content categorization
 */
export function TopicSelector({ 
    value = [], 
    onChange, 
    maxTopics = 5, 
    disabled = false, 
    error,
    suggestions = []
}: TopicSelectorProps) {
    const [inputValue, setInputValue] = useState('');
    const [showSuggestions, setShowSuggestions] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    // Common topic suggestions for better AEO
    const defaultSuggestions = [
        'Technology', 'Business', 'Marketing', 'Web Development', 'Design',
        'Programming', 'AI & Machine Learning', 'Data Science', 'Security',
        'Mobile Development', 'Cloud Computing', 'DevOps', 'Analytics',
        'User Experience', 'Content Strategy', 'SEO', 'Social Media',
        'E-commerce', 'Startup', 'Innovation', 'Digital Transformation'
    ];

    const allSuggestions = [...new Set([...suggestions, ...defaultSuggestions])];
    
    const filteredSuggestions = allSuggestions.filter(suggestion => 
        suggestion.toLowerCase().includes(inputValue.toLowerCase()) &&
        !value.includes(suggestion) &&
        inputValue.trim() !== ''
    ).slice(0, 8);

    const addTopic = (topic: string) => {
        const trimmedTopic = topic.trim();
        
        if (!trimmedTopic) return;
        if (value.includes(trimmedTopic)) return;
        if (value.length >= maxTopics) return;
        
        // Capitalize first letter of each word for consistency
        const formattedTopic = trimmedTopic
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
        
        onChange([...value, formattedTopic]);
        setInputValue('');
        setShowSuggestions(false);
    };

    const removeTopic = (topicToRemove: string) => {
        onChange(value.filter(topic => topic !== topicToRemove));
    };

    const handleInputKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (inputValue.trim()) {
                addTopic(inputValue);
            }
        } else if (e.key === 'Escape') {
            setShowSuggestions(false);
            inputRef.current?.blur();
        } else if (e.key === 'Backspace' && !inputValue && value.length > 0) {
            // Remove last topic if input is empty and backspace is pressed
            removeTopic(value[value.length - 1]);
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
        // Delay hiding suggestions to allow clicking on them
        setTimeout(() => setShowSuggestions(false), 200);
    };

    // Click outside to close suggestions
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (inputRef.current && !inputRef.current.contains(event.target as Node)) {
                setShowSuggestions(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const getTopicColor = (topic: string) => {
        const colors = [
            'bg-blue-100 text-blue-800',
            'bg-green-100 text-green-800', 
            'bg-purple-100 text-purple-800',
            'bg-orange-100 text-orange-800',
            'bg-pink-100 text-pink-800'
        ];
        
        // Use topic string to consistently assign colors
        const index = topic.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % colors.length;
        return colors[index];
    };

    return (
        <Card className="w-full">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Hash className="h-5 w-5 text-green-600" />
                        <CardTitle>Content Topics</CardTitle>
                        <Badge variant="secondary">
                            {value.length}/{maxTopics}
                        </Badge>
                    </div>
                </div>
                <CardDescription>
                    Add topics to categorize your content and improve AI search discoverability. 
                    Topics generate breadcrumb navigation and help search engines understand your content context.
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4">
                {error && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {/* Topic Input */}
                <div className="space-y-2">
                    <Label htmlFor="topic-input">Add Topics</Label>
                    <div className="relative">
                        <Input
                            ref={inputRef}
                            id="topic-input"
                            value={inputValue}
                            onChange={handleInputChange}
                            onKeyDown={handleInputKeyDown}
                            onFocus={handleInputFocus}
                            onBlur={handleInputBlur}
                            placeholder={value.length < maxTopics ? "Type a topic and press Enter..." : "Maximum topics reached"}
                            disabled={disabled || value.length >= maxTopics}
                            className="pr-10"
                        />
                        {inputValue && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                className="absolute right-1 top-1 h-8 w-8 p-0"
                                onClick={() => addTopic(inputValue)}
                                disabled={disabled || value.length >= maxTopics}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        )}

                        {/* Suggestions Dropdown */}
                        {showSuggestions && filteredSuggestions.length > 0 && (
                            <div className="absolute z-10 w-full mt-1 bg-white border border-border rounded-md shadow-lg max-h-48 overflow-auto">
                                {filteredSuggestions.map((suggestion, index) => (
                                    <button
                                        key={suggestion}
                                        type="button"
                                        className="w-full px-3 py-2 text-left hover:bg-muted text-sm border-none bg-transparent cursor-pointer"
                                        onClick={() => addTopic(suggestion)}
                                        disabled={disabled}
                                    >
                                        <div className="flex items-center space-x-2">
                                            <Tag className="h-3 w-3 text-muted-foreground" />
                                            <span>{suggestion}</span>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                    <p className="text-xs text-muted-foreground">
                        Press Enter to add, or click suggestions below
                    </p>
                </div>

                {/* Selected Topics */}
                {value.length > 0 && (
                    <div className="space-y-2">
                        <Label>Selected Topics</Label>
                        <div className="flex flex-wrap gap-2">
                            {value.map((topic) => (
                                <Badge
                                    key={topic}
                                    variant="secondary"
                                    className={`${getTopicColor(topic)} flex items-center space-x-1 pr-1`}
                                >
                                    <span>{topic}</span>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="h-4 w-4 p-0 hover:bg-transparent"
                                        onClick={() => removeTopic(topic)}
                                        disabled={disabled}
                                    >
                                        <X className="h-3 w-3" />
                                    </Button>
                                </Badge>
                            ))}
                        </div>
                    </div>
                )}

                {/* Popular Suggestions */}
                {value.length < maxTopics && inputValue === '' && (
                    <div className="space-y-2">
                        <Label className="flex items-center space-x-1">
                            <Lightbulb className="h-4 w-4 text-yellow-500" />
                            <span>Popular Topics</span>
                        </Label>
                        <div className="flex flex-wrap gap-2">
                            {allSuggestions
                                .filter(suggestion => !value.includes(suggestion))
                                .slice(0, 8)
                                .map((suggestion) => (
                                    <Button
                                        key={suggestion}
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        className="h-7 text-xs"
                                        onClick={() => addTopic(suggestion)}
                                        disabled={disabled || value.length >= maxTopics}
                                    >
                                        <Plus className="h-3 w-3 mr-1" />
                                        {suggestion}
                                    </Button>
                                ))
                            }
                        </div>
                    </div>
                )}

                {/* AEO Benefits Info */}
                {value.length > 0 && (
                    <Alert>
                        <Hash className="h-4 w-4" />
                        <AlertDescription>
                            <strong>AEO Benefits:</strong> {value.length} topic{value.length > 1 ? 's' : ''} will enhance content categorization,
                            generate structured breadcrumb navigation, and help AI search engines understand your content's subject matter.
                        </AlertDescription>
                    </Alert>
                )}

                {value.length === maxTopics && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>
                            Maximum {maxTopics} topics reached. Remove a topic to add a new one.
                        </AlertDescription>
                    </Alert>
                )}
            </CardContent>
        </Card>
    );
}

export default TopicSelector;