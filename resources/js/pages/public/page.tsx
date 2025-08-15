import { Head } from '@inertiajs/react';
import { TemplateRenderer, initializeCoreSystem, BlockConfig, ContentEntity } from '@/core';
import { useEffect } from 'react';

interface PageData extends ContentEntity {
    template: string;
    layout?: string;
    theme?: string;
    blocks: BlockConfig[];
    template_config: Record<string, unknown>;
}

interface SEOData {
    title: string;
    description?: string;
    keywords?: string;
    canonical: string;
    ogType: string;
    ogTitle: string;
    ogDescription?: string;
    ogUrl: string;
    twitterCard: string;
    twitterTitle: string;
    twitterDescription?: string;
}

interface Props {
    page: PageData;
    schemaData: Record<string, unknown>;
    seoData: SEOData;
}

export default function PublicPage({ page, schemaData, seoData }: Props) {
    // Initialize the core template system
    useEffect(() => {
        initializeCoreSystem();
    }, []);

    return (
        <>
            <Head>
                <title>{seoData.title}</title>
                {seoData.description && <meta name="description" content={seoData.description} />}
                {seoData.keywords && <meta name="keywords" content={seoData.keywords} />}
                
                {/* Canonical URL */}
                <link rel="canonical" href={seoData.canonical} />
                
                {/* Open Graph / Facebook */}
                <meta property="og:type" content={seoData.ogType} />
                <meta property="og:title" content={seoData.ogTitle} />
                {seoData.ogDescription && <meta property="og:description" content={seoData.ogDescription} />}
                <meta property="og:url" content={seoData.ogUrl} />
                <meta property="og:site_name" content={import.meta.env.VITE_APP_NAME || 'Thorium90'} />
                
                {/* Twitter */}
                <meta name="twitter:card" content={seoData.twitterCard} />
                <meta name="twitter:title" content={seoData.twitterTitle} />
                {seoData.twitterDescription && <meta name="twitter:description" content={seoData.twitterDescription} />}
                
                {/* Schema.org structured data */}
                <script type="application/ld+json">
                    {JSON.stringify(schemaData)}
                </script>
                
                {/* Viewport and responsive */}
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                
                {/* Additional SEO meta tags */}
                <meta name="robots" content="index, follow" />
                <meta name="author" content={page.user?.name || 'Thorium90'} />
                <meta name="generator" content="Thorium90 CMS" />
                
                {/* Article specific meta tags */}
                {page.published_at && <meta property="article:published_time" content={page.published_at} />}
                {page.updated_at && <meta property="article:modified_time" content={page.updated_at} />}
                {page.user?.name && <meta property="article:author" content={page.user.name} />}
            </Head>

            <TemplateRenderer
                content={page}
                templateId={page.template}
                layout={page.layout}
                theme={page.theme}
                blocks={page.blocks}
                config={page.template_config}
            />
        </>
    );
}
