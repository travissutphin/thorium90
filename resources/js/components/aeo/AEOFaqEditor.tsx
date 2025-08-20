import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Plus, X, HelpCircle, MessageSquare, AlertCircle } from 'lucide-react';
import { FAQItem } from '@/types';

interface AEOFaqEditorProps {
    value: FAQItem[];
    onChange: (faqs: FAQItem[]) => void;
    maxItems?: number;
    disabled?: boolean;
    error?: string;
}

/**
 * AEOFaqEditor Component
 * 
 * Provides an interface for managing FAQ (Frequently Asked Questions) content
 * that generates Schema.org FAQPage markup for Answer Engine Optimization.
 * 
 * Features:
 * - Add/remove/edit FAQ items
 * - Question and answer validation
 * - Character limits and guidance
 * - Real-time FAQ count tracking
 * - Schema.org structured data generation
 * 
 * Integration:
 * - Works with existing form validation
 * - Generates FAQPageSchema for schema_data
 * - Follows component patterns from /docs
 */
export function AEOFaqEditor({ 
    value = [], 
    onChange, 
    maxItems = 10, 
    disabled = false, 
    error 
}: AEOFaqEditorProps) {
    const [expandedItems, setExpandedItems] = useState<Set<string>>(new Set());

    const addFAQItem = () => {
        if (value.length >= maxItems) return;
        
        const newItem: FAQItem = {
            id: crypto.randomUUID(),
            question: '',
            answer: ''
        };
        
        onChange([...value, newItem]);
        setExpandedItems(prev => new Set([...prev, newItem.id]));
    };

    const removeFAQItem = (id: string) => {
        onChange(value.filter(item => item.id !== id));
        setExpandedItems(prev => {
            const newSet = new Set(prev);
            newSet.delete(id);
            return newSet;
        });
    };

    const updateFAQItem = (id: string, field: 'question' | 'answer', newValue: string) => {
        onChange(value.map(item => 
            item.id === id ? { ...item, [field]: newValue } : item
        ));
    };

    const toggleExpanded = (id: string) => {
        setExpandedItems(prev => {
            const newSet = new Set(prev);
            if (newSet.has(id)) {
                newSet.delete(id);
            } else {
                newSet.add(id);
            }
            return newSet;
        });
    };

    const getValidationErrors = (item: FAQItem) => {
        const errors: string[] = [];
        
        if (!item.question.trim()) {
            errors.push('Question is required');
        } else if (item.question.length < 10) {
            errors.push('Question should be at least 10 characters');
        } else if (item.question.length > 300) {
            errors.push('Question should be less than 300 characters');
        }
        
        if (!item.answer.trim()) {
            errors.push('Answer is required');
        } else if (item.answer.length < 20) {
            errors.push('Answer should be at least 20 characters');
        } else if (item.answer.length > 1000) {
            errors.push('Answer should be less than 1000 characters');
        }
        
        return errors;
    };

    const validItemsCount = value.filter(item => 
        item.question.trim() && item.answer.trim()
    ).length;

    return (
        <Card className="w-full">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <MessageSquare className="h-5 w-5 text-blue-600" />
                        <CardTitle>FAQ Section</CardTitle>
                        <Badge variant="secondary">
                            {validItemsCount}/{value.length} Valid
                        </Badge>
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={addFAQItem}
                        disabled={disabled || value.length >= maxItems}
                        className="flex items-center space-x-1"
                    >
                        <Plus className="h-4 w-4" />
                        <span>Add FAQ</span>
                    </Button>
                </div>
                <CardDescription>
                    Create frequently asked questions to enhance your content for AI search engines. 
                    FAQ content generates Schema.org FAQPage markup for better discoverability.
                    {maxItems > 0 && (
                        <span className="block mt-1 text-sm text-muted-foreground">
                            Maximum {maxItems} items allowed
                        </span>
                    )}
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4">
                {error && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {value.length === 0 ? (
                    <div className="text-center py-8 border-2 border-dashed border-muted rounded-lg">
                        <HelpCircle className="h-8 w-8 mx-auto mb-2 text-muted-foreground" />
                        <p className="text-muted-foreground mb-3">No FAQ items yet</p>
                        <Button 
                            type="button"
                            variant="outline" 
                            onClick={addFAQItem}
                            disabled={disabled}
                        >
                            <Plus className="h-4 w-4 mr-2" />
                            Add Your First FAQ
                        </Button>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {value.map((item, index) => {
                            const isExpanded = expandedItems.has(item.id);
                            const validationErrors = getValidationErrors(item);
                            const hasErrors = validationErrors.length > 0;
                            
                            return (
                                <Card key={item.id} className={`transition-all ${hasErrors ? 'border-red-200' : 'border-border'}`}>
                                    <CardHeader className="pb-3">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center space-x-2">
                                                <Badge variant="outline" className="text-xs">
                                                    FAQ #{index + 1}
                                                </Badge>
                                                {hasErrors && (
                                                    <Badge variant="destructive" className="text-xs">
                                                        {validationErrors.length} Error{validationErrors.length > 1 ? 's' : ''}
                                                    </Badge>
                                                )}
                                            </div>
                                            <div className="flex items-center space-x-1">
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => toggleExpanded(item.id)}
                                                    disabled={disabled}
                                                >
                                                    {isExpanded ? 'Collapse' : 'Expand'}
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => removeFAQItem(item.id)}
                                                    disabled={disabled}
                                                    className="text-red-600 hover:text-red-700"
                                                >
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>

                                        {!isExpanded && (
                                            <div className="text-sm text-muted-foreground">
                                                <p className="truncate">
                                                    <strong>Q:</strong> {item.question || 'No question set'}
                                                </p>
                                                <p className="truncate">
                                                    <strong>A:</strong> {item.answer || 'No answer set'}
                                                </p>
                                            </div>
                                        )}
                                    </CardHeader>

                                    {isExpanded && (
                                        <CardContent className="pt-0 space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor={`question-${item.id}`}>
                                                    Question
                                                    <span className="text-red-500 ml-1">*</span>
                                                </Label>
                                                <Input
                                                    id={`question-${item.id}`}
                                                    value={item.question}
                                                    onChange={(e) => updateFAQItem(item.id, 'question', e.target.value)}
                                                    placeholder="What question do users frequently ask?"
                                                    disabled={disabled}
                                                    className={hasErrors && !item.question.trim() ? 'border-red-300' : ''}
                                                />
                                                <div className="flex justify-between text-xs text-muted-foreground">
                                                    <span>Make it clear and specific</span>
                                                    <span>{item.question.length}/300</span>
                                                </div>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor={`answer-${item.id}`}>
                                                    Answer
                                                    <span className="text-red-500 ml-1">*</span>
                                                </Label>
                                                <Textarea
                                                    id={`answer-${item.id}`}
                                                    value={item.answer}
                                                    onChange={(e) => updateFAQItem(item.id, 'answer', e.target.value)}
                                                    placeholder="Provide a comprehensive answer to the question..."
                                                    rows={4}
                                                    disabled={disabled}
                                                    className={hasErrors && !item.answer.trim() ? 'border-red-300' : ''}
                                                />
                                                <div className="flex justify-between text-xs text-muted-foreground">
                                                    <span>Be thorough and helpful</span>
                                                    <span>{item.answer.length}/1000</span>
                                                </div>
                                            </div>

                                            {hasErrors && (
                                                <Alert variant="destructive">
                                                    <AlertCircle className="h-4 w-4" />
                                                    <AlertDescription>
                                                        <ul className="list-disc list-inside space-y-1">
                                                            {validationErrors.map((error, idx) => (
                                                                <li key={idx}>{error}</li>
                                                            ))}
                                                        </ul>
                                                    </AlertDescription>
                                                </Alert>
                                            )}
                                        </CardContent>
                                    )}
                                </Card>
                            );
                        })}
                    </div>
                )}

                {value.length > 0 && value.length < maxItems && (
                    <Button
                        type="button"
                        variant="outline"
                        className="w-full"
                        onClick={addFAQItem}
                        disabled={disabled}
                    >
                        <Plus className="h-4 w-4 mr-2" />
                        Add Another FAQ ({value.length}/{maxItems})
                    </Button>
                )}

                {validItemsCount > 0 && (
                    <Alert>
                        <MessageSquare className="h-4 w-4" />
                        <AlertDescription>
                            <strong>AEO Benefit:</strong> {validItemsCount} FAQ item{validItemsCount > 1 ? 's' : ''} will generate 
                            Schema.org FAQPage markup, helping AI search engines understand and feature your content in answer boxes.
                        </AlertDescription>
                    </Alert>
                )}
            </CardContent>
        </Card>
    );
}

export default AEOFaqEditor;