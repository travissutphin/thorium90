import { TemplateRegistry, ContentEntity } from './templates/TemplateRegistry';
import { LayoutRegistry } from './layouts/LayoutRegistry';
import { BlockRegistry } from './blocks/BlockRegistry';

// Import core layouts
import { DefaultLayout } from './layouts/layouts/DefaultLayout';
import { SidebarLayout } from './layouts/layouts/SidebarLayout';
import { FullWidthLayout } from './layouts/layouts/FullWidthLayout';

// Import core blocks
import { HeroBlock } from './blocks/core/HeroBlock';
import { ContentBlock } from './blocks/core/ContentBlock';

// Import existing page templates (we'll adapt them)
import { BasePageTemplate } from '@/components/page-templates/BasePageTemplate';

/**
 * Initialize the core template system
 * This function registers all core templates, layouts, and blocks
 */
export function initializeCoreSystem(): void {
    // Register core layouts
    LayoutRegistry.registerMultiple([
        {
            id: 'default',
            name: 'Default Layout',
            description: 'Simple centered layout with container',
            plugin: 'core',
            category: 'page',
            config: {
                name: 'Default Layout',
                sections: ['main'],
                defaultSections: {
                    main: 'content'
                }
            },
            component: DefaultLayout,
            isActive: true
        },
        {
            id: 'sidebar',
            name: 'Sidebar Layout',
            description: 'Layout with main content and sidebar',
            plugin: 'core',
            category: 'page',
            config: {
                name: 'Sidebar Layout',
                sections: ['main', 'sidebar'],
                defaultSections: {
                    main: 'content',
                    sidebar: 'related'
                }
            },
            component: SidebarLayout,
            isActive: true
        },
        {
            id: 'full-width',
            name: 'Full Width Layout',
            description: 'Full width layout without container',
            plugin: 'core',
            category: 'page',
            config: {
                name: 'Full Width Layout',
                sections: ['main'],
                defaultSections: {
                    main: 'content'
                }
            },
            component: FullWidthLayout,
            isActive: true
        }
    ]);

    // Register core blocks
    BlockRegistry.registerMultiple([
        {
            id: 'hero',
            name: 'Hero Section',
            description: 'Large banner section with title, subtitle, and call-to-action',
            plugin: 'core',
            category: 'hero',
            component: HeroBlock,
            defaultConfig: {
                height: 'lg',
                alignment: 'center',
                showCTA: true,
                ctaText: 'Get Started',
                secondaryCTA: false,
                secondaryCTAText: 'Learn More'
            },
            configSchema: {
                type: 'object',
                properties: {
                    height: {
                        type: 'string',
                        enum: ['sm', 'md', 'lg', 'xl', 'full'],
                        default: 'lg'
                    },
                    alignment: {
                        type: 'string',
                        enum: ['left', 'center', 'right'],
                        default: 'center'
                    },
                    backgroundImage: {
                        type: 'string',
                        format: 'uri'
                    },
                    showCTA: {
                        type: 'boolean',
                        default: true
                    },
                    ctaText: {
                        type: 'string',
                        default: 'Get Started'
                    },
                    secondaryCTA: {
                        type: 'boolean',
                        default: false
                    },
                    secondaryCTAText: {
                        type: 'string',
                        default: 'Learn More'
                    }
                }
            },
            isActive: true
        },
        {
            id: 'content',
            name: 'Content Block',
            description: 'Main content area with optional title and meta information',
            plugin: 'core',
            category: 'content',
            component: ContentBlock,
            defaultConfig: {
                showTitle: true,
                showMeta: true
            },
            configSchema: {
                type: 'object',
                properties: {
                    showTitle: {
                        type: 'boolean',
                        default: true
                    },
                    showMeta: {
                        type: 'boolean',
                        default: true
                    }
                }
            },
            isActive: true
        }
    ]);

    // Create a wrapper for the existing BasePageTemplate to work with the new system
    const CorePageTemplate = ({ content, layout }: { content: ContentEntity; layout?: string }) => {
        // Map layout to valid template values
        const templateMap: Record<string, 'default' | 'sidebar' | 'full-width' | 'hero' | 'landing'> = {
            'default': 'default',
            'sidebar': 'sidebar',
            'full-width': 'full-width',
            'hero': 'hero',
            'landing': 'landing'
        };

        const validTemplate = templateMap[layout || 'default'] || 'default';

        // Convert new system props to BasePageTemplate props
        const templateProps = {
            page: {
                ...content,
                reading_time: 5, // Default reading time
                is_featured: false,
                status: 'published',
                meta_title: content.meta.title,
                meta_description: content.meta.description,
                meta_keywords: content.meta.keywords,
                schema_type: 'WebPage',
                user: content.user || { name: 'Unknown' }
            },
            template: validTemplate,
            showHeader: true,
            showFooter: true,
            showSidebar: layout === 'sidebar',
            showNavigation: true,
            children: <div className="template-content">
                {typeof content.content === 'string' ? (
                    <div dangerouslySetInnerHTML={{ __html: content.content }} />
                ) : (
                    <pre>{JSON.stringify(content.content, null, 2)}</pre>
                )}
            </div>
        };

        return <BasePageTemplate {...templateProps} />;
    };

    // Register core page template
    TemplateRegistry.register({
        id: 'core-page',
        name: 'Core Page Template',
        description: 'Standard page template with flexible layout options',
        plugin: 'core',
        category: 'page',
        layouts: ['default', 'sidebar', 'full-width'],
        blocks: ['hero', 'content'],
        themes: ['default'],
        config: {
            layouts: ['default', 'sidebar', 'full-width'],
            blocks: ['hero', 'content'],
            themes: ['default'],
            defaultLayout: 'default',
            defaultTheme: 'default'
        },
        component: CorePageTemplate,
        isActive: true
    });

    // Import and register client templates
    try {
        // Dynamically import client templates if they exist
        import('@/templates/register').then(() => {
            console.log('Client templates registered');
        }).catch(() => {
            console.log('No client templates found - using core templates only');
        });
    } catch (error) {
        console.log('Client templates not available:', error);
    }

    console.log('Core template system initialized');
    console.log('Templates:', TemplateRegistry.getStats());
    console.log('Layouts:', LayoutRegistry.getStats());
    console.log('Blocks:', BlockRegistry.getStats());
}

// Export registries for use in other parts of the application
export { TemplateRegistry, LayoutRegistry, BlockRegistry };

// Export core components
export { TemplateRenderer, useTemplate, useTemplateSelection } from './templates/TemplateRenderer';
export { ThemeProvider, useTheme } from './theme/ThemeProvider';

// Export types
export type { 
    UniversalTemplate, 
    TemplateProps, 
    TemplateConfig, 
    BlockConfig, 
    ContentEntity 
} from './templates/TemplateRegistry';

export type { 
    UniversalLayout, 
    LayoutProps, 
    LayoutConfig 
} from './layouts/LayoutRegistry';

export type { 
    UniversalBlock, 
    BlockProps 
} from './blocks/BlockRegistry';

export type { Theme } from './theme/ThemeProvider';
