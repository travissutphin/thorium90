# Flexible Page Template System Guide

## 🎯 **Overview**

The Flexible Page Template System provides a consistent, maintainable, and flexible way to create different types of pages while maintaining design consistency across your website. It follows the established patterns from your Development Workflow and integrates seamlessly with your existing Laravel + React + Inertia.js architecture.

## 🏗️ **Architecture**

### **Core Components**

1. **`BasePageTemplate`** - The foundation template that handles layout, SEO, and common functionality
2. **`PageHeader`** - Flexible header component with multiple template variations
3. **`PageSidebar`** - Configurable sidebar with different content types
4. **`PageFooter`** - Adaptive footer based on template type
5. **`PageNavigation`** - Responsive navigation with template-specific behavior
6. **`PageContent`** - Main content area with flexible layouts

### **Template Types**

- **`default`** - Standard layout with header, content, sidebar, and footer
- **`hero`** - Hero section with large title and background
- **`sidebar`** - Content with prominent sidebar
- **`full-width`** - Full-width content without sidebar
- **`landing`** - Landing page style with minimal navigation

## 🚀 **Quick Start**

### **Basic Usage**

```tsx
import { BasePageTemplate } from '@/components/page-templates';

export default function MyPage({ page, schemaData }) {
    return (
        <BasePageTemplate
            page={page}
            schemaData={schemaData}
            template="default"
        >
            <div className="prose prose-gray dark:prose-invert max-w-none">
                <div dangerouslySetInnerHTML={{ __html: page.content }} />
            </div>
        </BasePageTemplate>
    );
}
```

### **Using Specific Templates**

```tsx
import { HomePageTemplate } from '@/components/page-templates';

export default function HomePage({ page, schemaData, featuredPages, stats }) {
    return (
        <HomePageTemplate
            page={page}
            schemaData={schemaData}
            featuredPages={featuredPages}
            stats={stats}
        />
    );
}
```

## 📋 **Template Options**

### **BasePageTemplate Props**

```tsx
interface BasePageTemplateProps {
    page: Page;                    // Page data
    schemaData?: Record<string, unknown>; // SEO structured data
    children: ReactNode;           // Main content
    template?: TemplateType;       // Template variation
    showHeader?: boolean;          // Show/hide header
    showFooter?: boolean;          // Show/hide footer
    showSidebar?: boolean;         // Show/hide sidebar
    showNavigation?: boolean;      // Show/hide navigation
    headerProps?: Record<string, unknown>;    // Header customization
    footerProps?: Record<string, unknown>;    // Footer customization
    sidebarProps?: Record<string, unknown>;   // Sidebar customization
    navigationProps?: Record<string, unknown>; // Navigation customization
}
```

### **Header Customization**

```tsx
<BasePageTemplate
    headerProps={{
        heroHeight: 'xl',           // 'sm' | 'md' | 'lg' | 'xl'
        titleSize: '4xl',           // 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl'
        showMeta: false,            // Show/hide meta information
        showActions: true,          // Show/hide action buttons
        heroImage: '/path/to/image.jpg', // Background image for hero
    }}
>
    {/* Content */}
</BasePageTemplate>
```

### **Sidebar Customization**

```tsx
<BasePageTemplate
    sidebarProps={{
        showPageInfo: true,         // Show page information
        showSeoInfo: true,          // Show SEO information
        showRelatedPages: false,    // Show related pages
        showAuthorInfo: true,       // Show author information
        customContent: <CustomSidebar />, // Custom sidebar content
    }}
>
    {/* Content */}
</BasePageTemplate>
```

## 🎨 **Pre-built Templates**

### **1. HomePageTemplate**

Perfect for landing pages with hero sections, stats, and featured content.

```tsx
<HomePageTemplate
    page={page}
    schemaData={schemaData}
    featuredPages={featuredPages}
    stats={{
        totalPages: 50,
        totalUsers: 1000,
        totalViews: 50000,
    }}
/>
```

**Features:**
- Hero header with large title
- Statistics cards
- Featured content grid
- Call-to-action sections

### **2. ServicesPageTemplate**

Ideal for service pages with pricing, features, and benefits.

```tsx
<ServicesPageTemplate
    page={page}
    schemaData={schemaData}
    services={[
        {
            id: 1,
            title: 'Basic Service',
            description: 'Essential features',
            features: ['Feature 1', 'Feature 2'],
            price: '$99',
            duration: '1 month',
        },
        // ... more services
    ]}
/>
```

**Features:**
- Service cards with pricing
- Feature lists
- Why choose us section
- Call-to-action

### **3. ContactPageTemplate**

Contact pages with forms and company information.

```tsx
<ContactPageTemplate
    page={page}
    schemaData={schemaData}
    contactInfo={{
        email: 'contact@example.com',
        phone: '+1 (555) 123-4567',
        address: '123 Business St, City, State',
        hours: 'Mon-Fri: 9 AM - 6 PM',
    }}
/>
```

**Features:**
- Contact form
- Company information
- Business hours
- FAQ section

### **4. AboutPageTemplate**

Company/about pages with team and company information.

```tsx
<AboutPageTemplate
    page={page}
    schemaData={schemaData}
    teamMembers={[
        {
            id: 1,
            name: 'John Doe',
            role: 'CEO',
            bio: 'Passionate leader...',
            email: 'john@example.com',
        },
        // ... more team members
    ]}
    companyStats={{
        founded: '2010',
        employees: 25,
        clients: 150,
        projects: 500,
    }}
/>
```

**Features:**
- Company statistics
- Mission and values
- Team member cards
- Company story

## 🔧 **Customization**

### **Custom Header**

```tsx
<BasePageTemplate
    headerProps={{
        customHeader: (
            <div className="bg-gradient-to-r from-purple-600 to-blue-600 text-white py-20">
                <div className="container mx-auto text-center">
                    <h1 className="text-5xl font-bold mb-4">{page.title}</h1>
                    <p className="text-xl">{page.excerpt}</p>
                </div>
            </div>
        ),
    }}
>
    {/* Content */}
</BasePageTemplate>
```

### **Custom Sidebar**

```tsx
<BasePageTemplate
    sidebarProps={{
        customContent: (
            <div className="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Custom Widget</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p>Your custom content here</p>
                    </CardContent>
                </Card>
            </div>
        ),
    }}
>
    {/* Content */}
</BasePageTemplate>
```

### **Custom Navigation**

```tsx
<BasePageTemplate
    navigationProps={{
        customNavigation: (
            <nav className="bg-primary text-primary-foreground">
                <div className="container mx-auto px-4 py-4">
                    <div className="flex items-center justify-between">
                        <h1 className="text-xl font-bold">{page.title}</h1>
                        <div className="flex gap-4">
                            <a href="/home" className="hover:text-primary-foreground/80">Home</a>
                            <a href="/about" className="hover:text-primary-foreground/80">About</a>
                        </div>
                    </div>
                </div>
            </nav>
        ),
    }}
>
    {/* Content */}
</BasePageTemplate>
```

## 📱 **Responsive Design**

All templates are fully responsive and include:

- **Mobile-first design** with progressive enhancement
- **Responsive grids** that adapt to screen sizes
- **Mobile navigation** with hamburger menu
- **Touch-friendly** buttons and interactions
- **Optimized spacing** for different devices

## 🎨 **Styling & Theming**

### **CSS Classes**

Templates use consistent CSS classes that integrate with your existing design system:

- **`page-template-{template}`** - Template-specific styling
- **`page-content-{template}`** - Content area styling
- **Consistent spacing** using Tailwind CSS utilities
- **Dark mode support** with CSS variables

### **Custom CSS**

Add custom styles by targeting template classes:

```css
.page-template-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.page-content-landing {
    max-width: 1200px;
    margin: 0 auto;
}
```

## 🔍 **SEO & Performance**

### **Built-in SEO Features**

- **Meta tags** (title, description, keywords)
- **Open Graph** tags for social sharing
- **Twitter Card** support
- **Schema.org** structured data
- **Canonical URLs**

### **Performance Optimizations**

- **Lazy loading** for images and components
- **Optimized bundle** splitting
- **Efficient re-renders** with React best practices
- **Minimal JavaScript** footprint

## 🧪 **Testing**

### **Component Testing**

```tsx
import { render, screen } from '@testing-library/react';
import { BasePageTemplate } from '@/components/page-templates';

test('renders page title correctly', () => {
    const mockPage = {
        title: 'Test Page',
        // ... other required props
    };
    
    render(
        <BasePageTemplate page={mockPage}>
            <div>Test content</div>
        </BasePageTemplate>
    );
    
    expect(screen.getByText('Test Page')).toBeInTheDocument();
});
```

### **Template Testing**

```tsx
import { render, screen } from '@testing-library/react';
import { HomePageTemplate } from '@/components/page-templates';

test('renders featured pages correctly', () => {
    const mockPage = { /* ... */ };
    const featuredPages = [
        { id: 1, title: 'Featured Page 1' },
        { id: 2, title: 'Featured Page 2' },
    ];
    
    render(
        <HomePageTemplate
            page={mockPage}
            featuredPages={featuredPages}
        />
    );
    
    expect(screen.getByText('Featured Page 1')).toBeInTheDocument();
    expect(screen.getByText('Featured Page 2')).toBeInTheDocument();
});
```

## 📚 **Best Practices**

### **1. Template Selection**

- **Use `hero`** for landing pages and important announcements
- **Use `default`** for standard content pages
- **Use `full-width`** for content-heavy pages
- **Use `landing`** for marketing pages

### **2. Content Organization**

- **Keep content focused** and relevant to the page purpose
- **Use consistent heading** hierarchy (H1, H2, H3)
- **Include clear calls-to-action** where appropriate
- **Optimize images** and media for web

### **3. Performance**

- **Lazy load** non-critical components
- **Optimize images** and use appropriate formats
- **Minimize JavaScript** in content areas
- **Use efficient** data fetching patterns

### **4. Accessibility**

- **Include proper** alt text for images
- **Use semantic** HTML elements
- **Ensure keyboard** navigation works
- **Test with** screen readers

## 🚨 **Common Issues & Solutions**

### **Issue: Template not rendering correctly**

**Solution:** Check that all required props are passed and page data structure matches expected format.

### **Issue: Styling conflicts**

**Solution:** Use template-specific CSS classes and avoid global style overrides.

### **Issue: SEO tags not working**

**Solution:** Ensure `schemaData` is properly formatted and `page` object contains required meta fields.

### **Issue: Responsive issues**

**Solution:** Test on multiple devices and use Tailwind's responsive utilities for custom styling.

## 🔄 **Migration from Existing Pages**

### **Step 1: Identify Template Type**

```tsx
// Old page structure
<AppLayout>
    <div className="prose">
        <h1>{page.title}</h1>
        <div dangerouslySetInnerHTML={{ __html: page.content }} />
    </div>
</AppLayout>

// New template structure
<BasePageTemplate page={page} template="default">
    <div className="prose prose-gray dark:prose-invert max-w-none">
        <div dangerouslySetInnerHTML={{ __html: page.content }} />
    </div>
</BasePageTemplate>
```

### **Step 2: Update Props**

```tsx
// Add required page data
const pageData = {
    ...page,
    reading_time: page.reading_time || 1,
    user: page.user || { name: 'Unknown Author' },
};

// Use template
<BasePageTemplate page={pageData} template="default">
    {/* Content */}
</BasePageTemplate>
```

### **Step 3: Customize as Needed**

```tsx
<BasePageTemplate
    page={pageData}
    template="hero"
    headerProps={{
        heroHeight: 'lg',
        titleSize: '3xl',
    }}
    showSidebar={false}
>
    {/* Content */}
</BasePageTemplate>
```

## 📖 **Examples**

### **Simple Blog Post**

```tsx
<BasePageTemplate page={page} template="default">
    <article className="prose prose-lg max-w-none">
        <div dangerouslySetInnerHTML={{ __html: page.content }} />
    </article>
</BasePageTemplate>
```

### **Landing Page**

```tsx
<BasePageTemplate
    page={page}
    template="hero"
    showSidebar={false}
    headerProps={{
        heroHeight: 'xl',
        titleSize: '4xl',
        showMeta: false,
    }}
>
    <div className="text-center">
        <h2 className="text-3xl font-bold mb-8">Welcome to Our Platform</h2>
        <p className="text-xl text-muted-foreground mb-12">
            Discover amazing features and benefits
        </p>
        <Button size="lg">Get Started</Button>
    </div>
</BasePageTemplate>
```

### **Product Page**

```tsx
<BasePageTemplate
    page={page}
    template="sidebar"
    sidebarProps={{
        showPageInfo: true,
        showSeoInfo: true,
        customContent: <ProductSidebar product={product} />,
    }}
>
    <div className="prose max-w-none">
        <div dangerouslySetInnerHTML={{ __html: page.content }} />
    </div>
</BasePageTemplate>
```

## 🎯 **Next Steps**

1. **Review existing pages** and identify template types
2. **Start with simple pages** using `BasePageTemplate`
3. **Gradually migrate** to specific templates
4. **Customize templates** based on your needs
5. **Test thoroughly** on different devices
6. **Optimize performance** and SEO

## 📞 **Support**

For questions or issues with the template system:

1. **Check this documentation** first
2. **Review the Development Workflow** for consistency
3. **Examine existing examples** in the codebase
4. **Create an issue** with detailed information

---

**Remember:** The template system is designed to be flexible while maintaining consistency. Start simple and gradually add complexity as needed.
