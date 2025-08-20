/**
 * AEO (Answer Engine Optimization) Components
 * 
 * This module exports all AEO-related components for easy importing
 * throughout the application. These components follow the established
 * patterns in /docs and integrate with the existing schema system.
 */

export { default as AEOFaqEditor } from './AEOFaqEditor';
export { default as TopicSelector } from './TopicSelector';
export { default as KeywordManager } from './KeywordManager';
export { default as ReadingTimeDisplay } from './ReadingTimeDisplay';
export { default as SchemaPreview } from './SchemaPreview';

// Export types for external use
export type {
    AEOFaqEditorProps,
    TopicSelectorProps, 
    KeywordManagerProps,
    ReadingTimeDisplayProps,
    SchemaPreviewProps
} from './types';

export type {
    FAQItem,
    AEOData,
    TopicSuggestion,
    KeywordSuggestion,
    AEOScore,
    SchemaPreviewData
} from './types';