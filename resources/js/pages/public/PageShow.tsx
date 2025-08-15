import { BasePageTemplate, HomePageTemplate, ServicesPageTemplate, ContactPageTemplate, AboutPageTemplate } from '@/components/page-templates';
import { type Page } from '@/types';

interface Props {
    page: Page;
    schemaData: Record<string, unknown>;
}

export default function PublicPageShow({ page, schemaData }: Props) {
    // Determine template based on page slug or content type
    const getTemplateForPage = (slug: string) => {
        switch (slug.toLowerCase()) {
            case 'home':
            case 'index':
                return 'home';
            case 'services':
                return 'services';
            case 'contact':
                return 'contact';
            case 'about':
            case 'about-us':
                return 'about';
            default:
                return 'default';
        }
    };

    const templateType = getTemplateForPage(page.slug);

    // Render appropriate template
    switch (templateType) {
        case 'home':
            return (
                <HomePageTemplate
                    page={page}
                    schemaData={schemaData}
                    featuredPages={[]} // You can pass featured pages from backend
                    stats={{
                        totalPages: 10,
                        totalUsers: 100,
                        totalViews: 1000,
                    }}
                />
            );

        case 'services':
            return (
                <ServicesPageTemplate
                    page={page}
                    schemaData={schemaData}
                    services={[]} // You can pass services from backend
                />
            );

        case 'contact':
            return (
                <ContactPageTemplate
                    page={page}
                    schemaData={schemaData}
                    contactInfo={{
                        email: 'contact@example.com',
                        phone: '+1 (555) 123-4567',
                        address: '123 Business Street, City, State 12345',
                        hours: 'Monday - Friday: 9:00 AM - 6:00 PM',
                    }}
                />
            );

        case 'about':
            return (
                <AboutPageTemplate
                    page={page}
                    schemaData={schemaData}
                    teamMembers={[]} // You can pass team members from backend
                    companyStats={{
                        founded: '2020',
                        employees: 15,
                        clients: 75,
                        projects: 200,
                    }}
                />
            );

        default:
            // Use base template for other pages
            return (
                <BasePageTemplate
                    page={page}
                    schemaData={schemaData}
                    template="default"
                    headerProps={{
                        showMeta: true,
                        showActions: true,
                    }}
                    sidebarProps={{
                        showPageInfo: true,
                        showSeoInfo: true,
                        showAuthorInfo: true,
                    }}
                >
                    <div className="prose prose-gray dark:prose-invert max-w-none">
                        <div dangerouslySetInnerHTML={{ __html: page.content }} />
                    </div>
                </BasePageTemplate>
            );
    }
}
