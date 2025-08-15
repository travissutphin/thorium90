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
import { HeaderBlock } from './blocks/core/HeaderBlock';
import { FooterBlock } from './blocks/core/FooterBlock';
import { BreadcrumbsBlock } from './blocks/core/BreadcrumbsBlock';
import { CTABlock } from './blocks/core/CTABlock';

// Import Phase 2 features
import { ContentZoneRegistry } from './zones/ContentZoneRegistry';
import { ThemeEngine } from './theme/ThemeEngine';
import { TemplateInheritance } from './templates/TemplateInheritance';

// Import Phase 3 features
import { PluginTemplateRegistry } from './plugins/PluginTemplateRegistry';
import { PluginLoader } from './plugins/PluginLoader';

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
        },
        {
            id: 'header',
            name: 'Header',
            description: 'Site header with navigation, logo, and user menu',
            plugin: 'core',
            category: 'navigation',
            component: HeaderBlock,
            defaultConfig: {
                variant: 'standard',
                siteName: 'Thorium90',
                navigation: [],
                showSearch: true,
                showUserMenu: true,
                showLanguageSwitcher: false,
                showNotifications: false,
                backgroundColor: 'white',
                textColor: 'gray-900',
                sticky: false
            },
            configSchema: {
                type: 'object',
                properties: {
                    variant: {
                        type: 'string',
                        enum: ['standard', 'sticky', 'mega', 'minimal'],
                        default: 'standard'
                    },
                    siteName: {
                        type: 'string',
                        default: 'Thorium90'
                    },
                    showSearch: {
                        type: 'boolean',
                        default: true
                    },
                    showUserMenu: {
                        type: 'boolean',
                        default: true
                    },
                    backgroundColor: {
                        type: 'string',
                        default: 'white'
                    },
                    textColor: {
                        type: 'string',
                        default: 'gray-900'
                    }
                }
            },
            isActive: true
        },
        {
            id: 'footer',
            name: 'Footer',
            description: 'Site footer with links, newsletter, and social media',
            plugin: 'core',
            category: 'navigation',
            component: FooterBlock,
            defaultConfig: {
                showNewsletter: true,
                showSocial: true,
                showBackToTop: true,
                backgroundColor: 'gray-900',
                textColor: 'white',
                columns: []
            },
            configSchema: {
                type: 'object',
                properties: {
                    showNewsletter: {
                        type: 'boolean',
                        default: true
                    },
                    showSocial: {
                        type: 'boolean',
                        default: true
                    },
                    showBackToTop: {
                        type: 'boolean',
                        default: true
                    },
                    backgroundColor: {
                        type: 'string',
                        default: 'gray-900'
                    },
                    textColor: {
                        type: 'string',
                        default: 'white'
                    }
                }
            },
            isActive: true
        },
        {
            id: 'breadcrumbs',
            name: 'Breadcrumbs',
            description: 'Navigation breadcrumbs showing page hierarchy',
            plugin: 'core',
            category: 'navigation',
            component: BreadcrumbsBlock,
            defaultConfig: {
                showHome: true,
                separator: '/',
                textColor: 'gray-600',
                activeColor: 'gray-900',
                linkColor: 'blue-600'
            },
            configSchema: {
                type: 'object',
                properties: {
                    showHome: {
                        type: 'boolean',
                        default: true
                    },
                    separator: {
                        type: 'string',
                        enum: ['/', '>', 'chevron'],
                        default: '/'
                    },
                    textColor: {
                        type: 'string',
                        default: 'gray-600'
                    }
                }
            },
            isActive: true
        },
        {
            id: 'cta',
            name: 'Call to Action',
            description: 'Call-to-action section with buttons and compelling copy',
            plugin: 'core',
            category: 'content',
            component: CTABlock,
            defaultConfig: {
                title: 'Ready to Get Started?',
                subtitle: 'Join thousands of satisfied customers today.',
                layout: 'centered',
                backgroundColor: 'blue-600',
                textColor: 'white',
                showIcon: true,
                icon: 'rocket',
                padding: 'large',
                buttons: [
                    { text: 'Get Started', href: '#', variant: 'primary' },
                    { text: 'Learn More', href: '#', variant: 'secondary' }
                ]
            },
            configSchema: {
                type: 'object',
                properties: {
                    title: {
                        type: 'string',
                        default: 'Ready to Get Started?'
                    },
                    subtitle: {
                        type: 'string',
                        default: 'Join thousands of satisfied customers today.'
                    },
                    layout: {
                        type: 'string',
                        enum: ['centered', 'left', 'right', 'split'],
                        default: 'centered'
                    },
                    backgroundColor: {
                        type: 'string',
                        default: 'blue-600'
                    },
                    showIcon: {
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
        blocks: ['header', 'hero', 'content', 'cta', 'footer', 'breadcrumbs'],
        themes: ['default'],
        config: {
            layouts: ['default', 'sidebar', 'full-width'],
            blocks: ['header', 'hero', 'content', 'cta', 'footer', 'breadcrumbs'],
            themes: ['default'],
            defaultLayout: 'default',
            defaultTheme: 'default'
        },
        component: CorePageTemplate,
        isActive: true
    });

    console.log('Core template system initialized');
    console.log('Templates:', TemplateRegistry.getStats());
    console.log('Layouts:', LayoutRegistry.getStats());
    console.log('Blocks:', BlockRegistry.getStats());
    console.log('Content Zones:', ContentZoneRegistry.getStats());
    console.log('Theme Engine:', ThemeEngine.getAllThemes().length, 'themes registered');
    console.log('Template Inheritance:', TemplateInheritance.getStats());
    console.log('Plugin System:', PluginTemplateRegistry.getStats());
    console.log('Plugin Loader:', PluginLoader.getStats());
}

// Export registries for use in other parts of the application
export { TemplateRegistry, LayoutRegistry, BlockRegistry, ContentZoneRegistry, ThemeEngine, TemplateInheritance, PluginTemplateRegistry, PluginLoader };

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
