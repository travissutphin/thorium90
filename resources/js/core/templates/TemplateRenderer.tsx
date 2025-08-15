import React, { Suspense, useMemo } from 'react';
import { TemplateRegistry, ContentEntity, BlockConfig } from './TemplateRegistry';

interface TemplateRendererProps {
    content: ContentEntity;
    templateId?: string;
    layout?: string;
    theme?: string;
    blocks?: BlockConfig[];
    config?: Record<string, unknown>;
    fallbackTemplate?: string;
    onError?: (error: Error, templateId: string) => void;
}

interface TemplateErrorBoundaryProps {
    children: React.ReactNode;
    templateId: string;
    onError?: (error: Error, templateId: string) => void;
}

class TemplateErrorBoundary extends React.Component<
    TemplateErrorBoundaryProps,
    { hasError: boolean; error?: Error }
> {
    constructor(props: TemplateErrorBoundaryProps) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error: Error) {
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
        console.error(`Template ${this.props.templateId} error:`, error, errorInfo);
        this.props.onError?.(error, this.props.templateId);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="template-error p-8 bg-red-50 border border-red-200 rounded-lg">
                    <h2 className="text-lg font-semibold text-red-800 mb-2">
                        Template Error
                    </h2>
                    <p className="text-red-600 mb-4">
                        Failed to render template: {this.props.templateId}
                    </p>
                    <details className="text-sm text-red-500">
                        <summary className="cursor-pointer font-medium">Error Details</summary>
                        <pre className="mt-2 p-2 bg-red-100 rounded text-xs overflow-auto">
                            {this.state.error?.message}
                        </pre>
                    </details>
                </div>
            );
        }

        return this.props.children;
    }
}

const TemplateLoadingFallback: React.FC<{ templateId: string }> = ({ templateId }) => (
    <div className="template-loading flex items-center justify-center p-8">
        <div className="text-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p className="text-gray-600">Loading template: {templateId}</p>
        </div>
    </div>
);

const DefaultTemplate: React.FC<{ content: ContentEntity }> = ({ content }) => (
    <div className="default-template max-w-4xl mx-auto p-6">
        <header className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900 mb-2">{content.title}</h1>
            {content.meta.description && (
                <p className="text-lg text-gray-600">{content.meta.description}</p>
            )}
        </header>
        <main className="prose prose-lg max-w-none">
            {typeof content.content === 'string' ? (
                <div dangerouslySetInnerHTML={{ __html: content.content }} />
            ) : (
                <pre className="bg-gray-100 p-4 rounded">
                    {JSON.stringify(content.content, null, 2)}
                </pre>
            )}
        </main>
        <footer className="mt-8 pt-4 border-t border-gray-200">
            <p className="text-sm text-gray-500">
                Published: {content.published_at ? new Date(content.published_at).toLocaleDateString() : 'Draft'}
            </p>
        </footer>
    </div>
);

export const TemplateRenderer: React.FC<TemplateRendererProps> = ({
    content,
    templateId,
    layout,
    theme,
    blocks,
    config,
    fallbackTemplate = 'default',
    onError
}) => {
    const resolvedTemplateId = templateId || content.template || fallbackTemplate;
    
    const templateData = useMemo(() => {
        const template = TemplateRegistry.get(resolvedTemplateId);
        
        if (!template) {
            console.warn(`Template ${resolvedTemplateId} not found, trying fallback`);
            const fallback = TemplateRegistry.get(fallbackTemplate);
            
            if (!fallback) {
                console.error(`Fallback template ${fallbackTemplate} not found`);
                return null;
            }
            
            return fallback;
        }
        
        if (!template.isActive) {
            console.warn(`Template ${resolvedTemplateId} is inactive`);
            return null;
        }
        
        return template;
    }, [resolvedTemplateId, fallbackTemplate]);

    const resolvedLayout = useMemo(() => {
        if (!templateData) return undefined;
        
        const requestedLayout = layout || content.layout;
        
        if (requestedLayout && templateData.layouts.includes(requestedLayout)) {
            return requestedLayout;
        }
        
        return templateData.config.defaultLayout || templateData.layouts[0];
    }, [templateData, layout, content.layout]);

    const resolvedTheme = useMemo(() => {
        if (!templateData) return undefined;
        
        const requestedTheme = theme || content.theme;
        
        if (requestedTheme && templateData.themes?.includes(requestedTheme)) {
            return requestedTheme;
        }
        
        return templateData.config.defaultTheme || templateData.themes?.[0];
    }, [templateData, theme, content.theme]);

    const resolvedBlocks = useMemo(() => {
        return blocks || content.blocks || [];
    }, [blocks, content.blocks]);

    const templateProps = useMemo(() => ({
        content,
        layout: resolvedLayout,
        theme: resolvedTheme,
        blocks: resolvedBlocks,
        config: {
            ...templateData?.config.settings,
            ...config
        }
    }), [content, resolvedLayout, resolvedTheme, resolvedBlocks, templateData, config]);

    // If no template found, use default template
    if (!templateData) {
        return (
            <TemplateErrorBoundary templateId={resolvedTemplateId} onError={onError}>
                <DefaultTemplate content={content} />
            </TemplateErrorBoundary>
        );
    }

    const TemplateComponent = templateData.component;

    const renderTemplate = () => (
        <TemplateErrorBoundary templateId={resolvedTemplateId} onError={onError}>
            <Suspense fallback={<TemplateLoadingFallback templateId={resolvedTemplateId} />}>
                <div 
                    className={`template-wrapper ${resolvedLayout ? `layout-${resolvedLayout}` : ''} ${resolvedTheme ? `theme-${resolvedTheme}` : ''}`}
                    data-template={resolvedTemplateId}
                    data-layout={resolvedLayout}
                    data-theme={resolvedTheme}
                >
                    <TemplateComponent {...templateProps} />
                </div>
            </Suspense>
        </TemplateErrorBoundary>
    );

    return renderTemplate();
};

// Hook for using template data in components
export const useTemplate = (templateId: string) => {
    return useMemo(() => {
        const template = TemplateRegistry.get(templateId);
        
        return {
            template,
            exists: !!template,
            isActive: template?.isActive ?? false,
            layouts: template?.layouts ?? [],
            blocks: template?.blocks ?? [],
            themes: template?.themes ?? [],
            config: template?.config
        };
    }, [templateId]);
};

// Hook for template selection logic
export const useTemplateSelection = (contentType: string) => {
    return useMemo(() => {
        const templates = TemplateRegistry.getByContentType(contentType);
        const options = TemplateRegistry.getSelectOptions(contentType);
        
        return {
            templates,
            options,
            getLayoutsForTemplate: (templateId: string) => 
                TemplateRegistry.getTemplateLayouts(templateId),
            getBlocksForTemplate: (templateId: string) => 
                TemplateRegistry.getTemplateBlocks(templateId),
            getThemesForTemplate: (templateId: string) => 
                TemplateRegistry.getTemplateThemes(templateId)
        };
    }, [contentType]);
};

export default TemplateRenderer;
