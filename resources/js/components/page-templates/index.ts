// Base Template Components
export { BasePageTemplate } from './BasePageTemplate';
export { PageHeader } from './PageHeader';
export { PageFooter } from './PageFooter';
export { PageSidebar } from './PageSidebar';
export { PageNavigation } from './PageNavigation';
export { PageContent } from './PageContent';

// Specific Page Templates
export { HomePageTemplate } from './templates/HomePageTemplate';
export { ServicesPageTemplate } from './templates/ServicesPageTemplate';
export { ContactPageTemplate } from './templates/ContactPageTemplate';
export { AboutPageTemplate } from './templates/AboutPageTemplate';

// Template Types
export type TemplateType = 'default' | 'hero' | 'sidebar' | 'full-width' | 'landing';

// Re-export types for convenience
export type { BasePageTemplateProps } from './BasePageTemplate';
export type { PageHeaderProps } from './PageHeader';
export type { PageFooterProps } from './PageFooter';
export type { PageSidebarProps } from './PageSidebar';
export type { PageNavigationProps } from './PageNavigation';
export type { PageContentProps } from './PageContent';
