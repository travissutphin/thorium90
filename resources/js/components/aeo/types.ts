/**
 * AEO (Answer Engine Optimization) Type Definitions
 * 
 * These types define the structure for AEO-related data and components,
 * following the existing schema.org and component patterns in the application.
 */

import { FAQItem } from '@/types';

// Re-export FAQItem for AEO components
export type { FAQItem };

export interface AEOData {
    topics: string[];
    keywords: string[];
    faq_data: FAQItem[];
    reading_time?: number;
    content_type: string;
    content_score?: number;
}

export interface TopicSuggestion {
    name: string;
    confidence: number;
    category: string;
}

export interface KeywordSuggestion {
    keyword: string;
    relevance: number;
    type: 'primary' | 'secondary' | 'long_tail';
}

export interface AEOScore {
    overall: number;
    breakdown: {
        topics: number;
        keywords: number;
        faq: number;
        structure: number;
        readability: number;
    };
    suggestions: string[];
}

export interface SchemaPreviewData {
    '@context': 'https://schema.org';
    '@type': string;
    [key: string]: any;
}

// Component Props
export interface AEOFaqEditorProps {
    value: FAQItem[];
    onChange: (faqs: FAQItem[]) => void;
    maxItems?: number;
    disabled?: boolean;
    error?: string;
}

export interface TopicSelectorProps {
    value: string[];
    onChange: (topics: string[]) => void;
    suggestions?: TopicSuggestion[];
    maxTopics?: number;
    disabled?: boolean;
    error?: string;
}

export interface KeywordManagerProps {
    value: string[];
    onChange: (keywords: string[]) => void;
    suggestions?: KeywordSuggestion[];
    maxKeywords?: number;
    disabled?: boolean;
    error?: string;
}

export interface ReadingTimeDisplayProps {
    content: string;
    readingTime?: number;
    wordsPerMinute?: number;
    showWordCount?: boolean;
}

export interface SchemaPreviewProps {
    schemaData: SchemaPreviewData;
    visible: boolean;
    onToggle: () => void;
}

export interface AEOScoreCardProps {
    score: AEOScore;
    loading?: boolean;
}