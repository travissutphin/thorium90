import React, { useState, useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Code, Eye, EyeOff, Copy, Check, ExternalLink, ChevronDown, ChevronRight, Info } from 'lucide-react';
import { SchemaData, FAQItem } from '@/types';

interface SchemaPreviewProps {
    schemaType: string;
    title: string;
    content: string;
    topics?: string[];
    keywords?: string[];
    faqData?: FAQItem[];
    readingTime?: number;
    visible?: boolean;
    onToggle?: () => void;
}

/**
 * SchemaPreview Component
 * 
 * Provides a live preview of the generated Schema.org JSON-LD markup
 * that will be included in the page head for SEO and AEO optimization.
 * 
 * Features:
 * - Real-time schema generation preview
 * - JSON-LD formatting and validation
 * - Copy to clipboard functionality
 * - Schema.org documentation links
 * - AEO property highlighting
 * - Collapsible sections for readability
 * 
 * Integration:
 * - Shows actual schema that will be output
 * - Validates required properties
 * - Demonstrates AEO enhancements
 */
export function SchemaPreview({ 
    schemaType, 
    title, 
    content, 
    topics = [], 
    keywords = [], 
    faqData = [], 
    readingTime,
    visible = false,
    onToggle 
}: SchemaPreviewProps) {
    const [copied, setCopied] = useState(false);
    const [expandedSections, setExpandedSections] = useState<Set<string>>(new Set(['main']));

    // Generate the actual schema that would be output
    const generatedSchema = useMemo(() => {
        const baseSchema: any = {
            '@context': 'https://schema.org',
            '@type': schemaType,
            name: title,
            description: content.replace(/<[^>]*>/g, '').substring(0, 160) + '...',
            url: typeof window !== 'undefined' ? window.location.href : '',
            datePublished: new Date().toISOString(),
            dateModified: new Date().toISOString(),
            author: {
                '@type': 'Person',
                name: 'Content Author'
            },
            publisher: {
                '@type': 'Organization',
                name: 'Your Organization',
                url: typeof window !== 'undefined' ? window.location.origin : ''
            }
        };

        // Add AEO enhancements based on schema type
        if (schemaType === 'Article' || schemaType === 'BlogPosting' || schemaType === 'NewsArticle') {
            baseSchema.headline = title;
            baseSchema.articleBody = content.replace(/<[^>]*>/g, '').substring(0, 200) + '...';
            
            if (content) {
                const wordCount = content.replace(/<[^>]*>/g, '').split(' ').length;
                baseSchema.wordCount = wordCount;
            }
        }

        // Add FAQ schema for FAQ pages
        if (schemaType === 'FAQPage' && faqData.length > 0) {
            baseSchema.mainEntity = faqData.map(faq => ({
                '@type': 'Question',
                name: faq.question,
                acceptedAnswer: {
                    '@type': 'Answer',
                    text: faq.answer
                }
            }));
        }

        // Add AEO-specific properties
        if (keywords.length > 0) {
            baseSchema.keywords = keywords.join(', ');
        }

        if (topics.length > 0) {
            baseSchema.about = topics.map(topic => ({
                '@type': 'Thing',
                name: topic
            }));

            // Generate breadcrumb based on topics
            if (topics.length > 0) {
                baseSchema.breadcrumb = {
                    '@type': 'BreadcrumbList',
                    itemListElement: [
                        {
                            '@type': 'ListItem',
                            position: 1,
                            name: 'Home',
                            item: typeof window !== 'undefined' ? window.location.origin : ''
                        },
                        ...topics.slice(0, 2).map((topic, index) => ({
                            '@type': 'ListItem',
                            position: index + 2,
                            name: topic,
                            item: `${typeof window !== 'undefined' ? window.location.origin : ''}/${topic.toLowerCase().replace(/\s+/g, '-')}`
                        })),
                        {
                            '@type': 'ListItem',
                            position: topics.slice(0, 2).length + 2,
                            name: title,
                            item: typeof window !== 'undefined' ? window.location.href : ''
                        }
                    ]
                };
            }
        }

        // Add reading time in ISO 8601 format
        if (readingTime) {
            baseSchema.timeRequired = `PT${readingTime}M`;
        }

        // Add language
        baseSchema.inLanguage = 'en';

        return baseSchema;
    }, [schemaType, title, content, topics, keywords, faqData, readingTime]);

    const copyToClipboard = async () => {
        try {
            const formattedJson = JSON.stringify(generatedSchema, null, 2);
            await navigator.clipboard.writeText(formattedJson);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Failed to copy schema to clipboard:', err);
        }
    };

    const toggleSection = (section: string) => {
        setExpandedSections(prev => {
            const newSet = new Set(prev);
            if (newSet.has(section)) {
                newSet.delete(section);
            } else {
                newSet.add(section);
            }
            return newSet;
        });
    };

    const getSchemaValidation = () => {
        const validation = {
            valid: true,
            warnings: [] as string[],
            errors: [] as string[]
        };

        // Required fields validation
        if (!title) validation.errors.push('Title is required');
        if (!content) validation.errors.push('Content is required');

        // Schema-specific validation
        if (schemaType === 'FAQPage' && faqData.length === 0) {
            validation.warnings.push('FAQ pages should have at least one question-answer pair');
        }

        if (schemaType === 'Article' && content.length < 300) {
            validation.warnings.push('Articles should have substantial content (300+ words)');
        }

        // AEO recommendations
        if (keywords.length === 0) {
            validation.warnings.push('Adding keywords will improve search discoverability');
        }

        if (topics.length === 0) {
            validation.warnings.push('Adding topics will enhance content categorization');
        }

        validation.valid = validation.errors.length === 0;
        return validation;
    };

    const validation = getSchemaValidation();

    const renderJsonValue = (key: string, value: any, depth = 0): React.ReactNode => {
        const indent = '  '.repeat(depth);
        
        if (value === null || value === undefined) {
            return <span className="text-gray-400">null</span>;
        }
        
        if (typeof value === 'string') {
            return <span className="text-green-600">"{value}"</span>;
        }
        
        if (typeof value === 'number' || typeof value === 'boolean') {
            return <span className="text-blue-600">{value.toString()}</span>;
        }
        
        if (Array.isArray(value)) {
            if (value.length === 0) return <span className="text-gray-400">[]</span>;
            
            return (
                <div>
                    <span>[</span>
                    {value.map((item, index) => (
                        <div key={index} className="ml-4">
                            {typeof item === 'object' ? (
                                <div>
                                    <span>{'{'}</span>
                                    {Object.entries(item).map(([k, v]) => (
                                        <div key={k} className="ml-4">
                                            <span className="text-purple-600">"{k}"</span>: {renderJsonValue(k, v, depth + 2)}
                                            {Object.keys(item).indexOf(k) < Object.keys(item).length - 1 && ','}
                                        </div>
                                    ))}
                                    <span>{'}'}</span>
                                </div>
                            ) : (
                                renderJsonValue('', item, depth + 1)
                            )}
                            {index < value.length - 1 && ','}
                        </div>
                    ))}
                    <span>]</span>
                </div>
            );
        }
        
        if (typeof value === 'object') {
            const entries = Object.entries(value);
            if (entries.length === 0) return <span className="text-gray-400">{'{}'}</span>;
            
            return (
                <div>
                    <span>{'{'}</span>
                    {entries.map(([k, v], index) => (
                        <div key={k} className="ml-4">
                            <span className="text-purple-600">"{k}"</span>: {renderJsonValue(k, v, depth + 1)}
                            {index < entries.length - 1 && ','}
                        </div>
                    ))}
                    <span>{'}'}</span>
                </div>
            );
        }
        
        return <span>{value.toString()}</span>;
    };

    if (!visible) {
        return (
            <Button
                type="button"
                variant="outline"
                onClick={onToggle}
                className="w-full"
            >
                <Eye className="h-4 w-4 mr-2" />
                Preview Schema Markup
            </Button>
        );
    }

    return (
        <Card className="w-full">
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Code className="h-5 w-5 text-indigo-600" />
                        <CardTitle>Schema.org JSON-LD Preview</CardTitle>
                        <Badge variant={validation.valid ? "default" : "destructive"}>
                            {validation.valid ? 'Valid' : `${validation.errors.length} Error${validation.errors.length > 1 ? 's' : ''}`}
                        </Badge>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={copyToClipboard}
                            className="flex items-center space-x-1"
                        >
                            {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                            <span>{copied ? 'Copied!' : 'Copy'}</span>
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={onToggle}
                        >
                            <EyeOff className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
                <CardDescription>
                    Live preview of the JSON-LD structured data that will be embedded in your page's head section.
                    This helps search engines and AI systems understand your content.
                </CardDescription>
            </CardHeader>

            <CardContent className="space-y-4">
                {/* Validation Results */}
                {validation.errors.length > 0 && (
                    <Alert variant="destructive">
                        <AlertDescription>
                            <strong>Schema Errors:</strong>
                            <ul className="list-disc list-inside mt-1">
                                {validation.errors.map((error, index) => (
                                    <li key={index}>{error}</li>
                                ))}
                            </ul>
                        </AlertDescription>
                    </Alert>
                )}

                {validation.warnings.length > 0 && (
                    <Alert>
                        <Info className="h-4 w-4" />
                        <AlertDescription>
                            <strong>AEO Recommendations:</strong>
                            <ul className="list-disc list-inside mt-1">
                                {validation.warnings.map((warning, index) => (
                                    <li key={index}>{warning}</li>
                                ))}
                            </ul>
                        </AlertDescription>
                    </Alert>
                )}

                {/* JSON-LD Output */}
                <div className="space-y-2">
                    <div className="flex items-center justify-between">
                        <h4 className="text-sm font-medium">Generated JSON-LD Markup</h4>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            asChild
                        >
                            <a
                                href={`https://search.google.com/test/rich-results`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center space-x-1"
                            >
                                <ExternalLink className="h-3 w-3" />
                                <span>Test in Google</span>
                            </a>
                        </Button>
                    </div>
                    
                    <div className="bg-gray-50 border border-border rounded-md p-4 overflow-auto max-h-96">
                        <pre className="text-xs font-mono">
                            <code>
                                <div className="text-gray-600">{'<script type="application/ld+json">'}</div>
                                <div className="text-black font-mono whitespace-pre-wrap">
                                    {JSON.stringify(generatedSchema, null, 2)}
                                </div>
                                <div className="text-gray-600">{'</script>'}</div>
                            </code>
                        </pre>
                    </div>
                </div>

                {/* AEO Properties Highlight */}
                <div className="space-y-2">
                    <h4 className="text-sm font-medium">AEO Enhancement Properties</h4>
                    <div className="grid grid-cols-2 gap-2 text-sm">
                        {keywords.length > 0 && (
                            <div className="flex items-center space-x-2 p-2 bg-green-50 rounded border border-green-200">
                                <Badge variant="secondary" className="text-xs">keywords</Badge>
                                <span className="text-green-700 text-xs">Enhanced discoverability</span>
                            </div>
                        )}
                        {topics.length > 0 && (
                            <div className="flex items-center space-x-2 p-2 bg-blue-50 rounded border border-blue-200">
                                <Badge variant="secondary" className="text-xs">about + breadcrumb</Badge>
                                <span className="text-blue-700 text-xs">Topic relationships</span>
                            </div>
                        )}
                        {faqData.length > 0 && (
                            <div className="flex items-center space-x-2 p-2 bg-purple-50 rounded border border-purple-200">
                                <Badge variant="secondary" className="text-xs">mainEntity (FAQ)</Badge>
                                <span className="text-purple-700 text-xs">Answer box eligibility</span>
                            </div>
                        )}
                        {readingTime && (
                            <div className="flex items-center space-x-2 p-2 bg-orange-50 rounded border border-orange-200">
                                <Badge variant="secondary" className="text-xs">timeRequired</Badge>
                                <span className="text-orange-700 text-xs">User experience data</span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Schema.org Documentation */}
                <Alert>
                    <Info className="h-4 w-4" />
                    <AlertDescription className="text-xs">
                        Learn more about <a href={`https://schema.org/${schemaType}`} target="_blank" rel="noopener noreferrer" className="underline">
                            {schemaType} schema properties
                        </a> and <a href="https://developers.google.com/search/docs/appearance/structured-data" target="_blank" rel="noopener noreferrer" className="underline">
                            structured data guidelines
                        </a>.
                    </AlertDescription>
                </Alert>
            </CardContent>
        </Card>
    );
}

export default SchemaPreview;