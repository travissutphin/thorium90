import { type ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import { PageHeader } from './PageHeader';
import { PageFooter } from './PageFooter';
import { PageSidebar } from './PageSidebar';
import { PageNavigation } from './PageNavigation';

export interface BasePageTemplateProps {
    page: {
        title: string;
        meta_title?: string;
        meta_description?: string;
        meta_keywords?: string;
        schema_type?: string;
        slug: string;
        status: string;
        is_featured: boolean;
        published_at?: string;
        user: { name: string };
        reading_time: number;
    };
    schemaData?: Record<string, unknown>;
    children: ReactNode;
    template?: 'default' | 'hero' | 'sidebar' | 'full-width' | 'landing';
    showHeader?: boolean;
    showFooter?: boolean;
    showSidebar?: boolean;
    showNavigation?: boolean;
    headerProps?: Record<string, unknown>;
    footerProps?: Record<string, unknown>;
    sidebarProps?: Record<string, unknown>;
    navigationProps?: Record<string, unknown>;
}

export function BasePageTemplate({
    page,
    schemaData,
    children,
    template = 'default',
    showHeader = true,
    showFooter = true,
    showSidebar = true,
    showNavigation = true,
    headerProps = {},
    footerProps = {},
    sidebarProps = {},
    navigationProps = {},
}: BasePageTemplateProps) {
    const pageUrl = `${window.location.origin}/pages/${page.slug}`;
    
    return (
        <>
            <Head>
                <title>{page.meta_title || page.title}</title>
                <meta name="description" content={page.meta_description} />
                {page.meta_keywords && <meta name="keywords" content={page.meta_keywords} />}
                
                {/* Open Graph / Facebook */}
                <meta property="og:type" content="article" />
                <meta property="og:title" content={page.meta_title || page.title} />
                <meta property="og:description" content={page.meta_description} />
                <meta property="og:url" content={pageUrl} />
                
                {/* Twitter */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={page.meta_title || page.title} />
                <meta name="twitter:description" content={page.meta_description} />
                
                {/* Schema.org structured data */}
                {schemaData && (
                    <script type="application/ld+json">
                        {JSON.stringify(schemaData)}
                    </script>
                )}
                
                {/* Canonical URL */}
                <link rel="canonical" href={pageUrl} />
            </Head>

            <div className={`min-h-screen bg-background page-template-${template}`}>
                {/* Navigation */}
                {showNavigation && (
                    <PageNavigation 
                        page={page}
                        {...navigationProps}
                    />
                )}

                {/* Header */}
                {showHeader && (
                    <PageHeader 
                        page={page}
                        template={template}
                        {...headerProps}
                    />
                )}

                {/* Main Content Area */}
                <main className={`page-content-${template}`}>
                    <div className="container mx-auto px-4 py-8">
                        <div className={`grid gap-8 ${showSidebar ? 'lg:grid-cols-4' : 'lg:grid-cols-1'}`}>
                            {/* Main Content */}
                            <div className={showSidebar ? 'lg:col-span-3' : 'lg:col-span-1'}>
                                {children}
                            </div>

                            {/* Sidebar */}
                            {showSidebar && (
                                <PageSidebar 
                                    page={page}
                                    template={template}
                                    {...sidebarProps}
                                />
                            )}
                        </div>
                    </div>
                </main>

                {/* Footer */}
                {showFooter && (
                    <PageFooter 
                        page={page}
                        template={template}
                        {...footerProps}
                    />
                )}
            </div>
        </>
    );
}
