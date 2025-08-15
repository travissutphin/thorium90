import { ComponentType } from 'react';

export interface TemplateConfig {
    layouts: string[];
    blocks: string[];
    themes?: string[];
    defaultLayout?: string;
    defaultTheme?: string;
    settings?: Record<string, unknown>;
}

export interface TemplateProps {
    content: ContentEntity;
    layout?: string;
    theme?: string;
    blocks?: BlockConfig[];
    config?: Record<string, unknown>;
    [key: string]: unknown;
}

export interface BlockConfig {
    type: string;
    position: number;
    config: Record<string, unknown>;
    content?: Record<string, unknown>;
}

export interface UniversalTemplate {
    id: string;
    name: string;
    description?: string;
    plugin: string; // 'core' | 'blog' | 'ecommerce' | 'classifieds'
    category: string; // 'page' | 'post' | 'product' | 'classified'
    layouts: string[];
    blocks: string[];
    themes?: string[];
    config: TemplateConfig;
    component: ComponentType<TemplateProps>;
    preview?: string; // Preview image URL
    isActive: boolean;
}

export interface ContentEntity {
    id: number;
    type: 'page' | 'post' | 'product' | 'classified';
    title: string;
    slug: string;
    content: string | Record<string, unknown>;
    template: string;
    layout?: string;
    theme?: string;
    blocks?: BlockConfig[];
    meta: {
        title?: string;
        description?: string;
        keywords?: string;
        [key: string]: unknown;
    };
    user?: {
        id: number;
        name: string;
    };
    status?: string;
    published_at?: string;
    created_at: string;
    updated_at: string;
}

class TemplateRegistryClass {
    private templates: Map<string, UniversalTemplate> = new Map();
    private pluginTemplates: Map<string, UniversalTemplate[]> = new Map();

    /**
     * Register a template
     */
    register(template: UniversalTemplate): void {
        if (this.templates.has(template.id)) {
            console.warn(`Template ${template.id} is already registered. Overwriting.`);
        }

        this.templates.set(template.id, template);

        // Group by plugin for easier management
        const pluginTemplates = this.pluginTemplates.get(template.plugin) || [];
        const existingIndex = pluginTemplates.findIndex(t => t.id === template.id);
        
        if (existingIndex >= 0) {
            pluginTemplates[existingIndex] = template;
        } else {
            pluginTemplates.push(template);
        }
        
        this.pluginTemplates.set(template.plugin, pluginTemplates);
    }

    /**
     * Register multiple templates
     */
    registerMultiple(templates: UniversalTemplate[]): void {
        templates.forEach(template => this.register(template));
    }

    /**
     * Get a template by ID
     */
    get(templateId: string): UniversalTemplate | undefined {
        return this.templates.get(templateId);
    }

    /**
     * Get all templates
     */
    getAll(): UniversalTemplate[] {
        return Array.from(this.templates.values());
    }

    /**
     * Get templates by plugin
     */
    getByPlugin(plugin: string): UniversalTemplate[] {
        return this.pluginTemplates.get(plugin) || [];
    }

    /**
     * Get templates by category
     */
    getByCategory(category: string): UniversalTemplate[] {
        return Array.from(this.templates.values()).filter(
            template => template.category === category
        );
    }

    /**
     * Get active templates only
     */
    getActive(): UniversalTemplate[] {
        return Array.from(this.templates.values()).filter(
            template => template.isActive
        );
    }

    /**
     * Get templates by content type
     */
    getByContentType(contentType: string): UniversalTemplate[] {
        return this.getByCategory(contentType).filter(template => template.isActive);
    }

    /**
     * Check if a template exists
     */
    has(templateId: string): boolean {
        return this.templates.has(templateId);
    }

    /**
     * Remove a template
     */
    unregister(templateId: string): boolean {
        const template = this.templates.get(templateId);
        if (!template) return false;

        this.templates.delete(templateId);

        // Remove from plugin templates
        const pluginTemplates = this.pluginTemplates.get(template.plugin) || [];
        const filteredTemplates = pluginTemplates.filter(t => t.id !== templateId);
        this.pluginTemplates.set(template.plugin, filteredTemplates);

        return true;
    }

    /**
     * Get template options for a select dropdown
     */
    getSelectOptions(contentType?: string): Array<{ value: string; label: string; description?: string }> {
        const templates = contentType 
            ? this.getByContentType(contentType)
            : this.getActive();

        return templates.map(template => ({
            value: template.id,
            label: template.name,
            description: template.description
        }));
    }

    /**
     * Get available layouts for a template
     */
    getTemplateLayouts(templateId: string): string[] {
        const template = this.get(templateId);
        return template?.layouts || [];
    }

    /**
     * Get available blocks for a template
     */
    getTemplateBlocks(templateId: string): string[] {
        const template = this.get(templateId);
        return template?.blocks || [];
    }

    /**
     * Get available themes for a template
     */
    getTemplateThemes(templateId: string): string[] {
        const template = this.get(templateId);
        return template?.themes || [];
    }

    /**
     * Validate template configuration
     */
    validateTemplate(template: Partial<UniversalTemplate>): string[] {
        const errors: string[] = [];

        if (!template.id) errors.push('Template ID is required');
        if (!template.name) errors.push('Template name is required');
        if (!template.plugin) errors.push('Template plugin is required');
        if (!template.category) errors.push('Template category is required');
        if (!template.component) errors.push('Template component is required');
        if (!template.layouts || template.layouts.length === 0) {
            errors.push('Template must have at least one layout');
        }

        return errors;
    }

    /**
     * Get template statistics
     */
    getStats(): {
        total: number;
        active: number;
        byPlugin: Record<string, number>;
        byCategory: Record<string, number>;
    } {
        const all = this.getAll();
        const active = this.getActive();

        const byPlugin: Record<string, number> = {};
        const byCategory: Record<string, number> = {};

        all.forEach(template => {
            byPlugin[template.plugin] = (byPlugin[template.plugin] || 0) + 1;
            byCategory[template.category] = (byCategory[template.category] || 0) + 1;
        });

        return {
            total: all.length,
            active: active.length,
            byPlugin,
            byCategory
        };
    }

    /**
     * Clear all templates (useful for testing)
     */
    clear(): void {
        this.templates.clear();
        this.pluginTemplates.clear();
    }

    /**
     * Export templates configuration
     */
    export(): Record<string, UniversalTemplate> {
        const exported: Record<string, UniversalTemplate> = {};
        this.templates.forEach((template, id) => {
            exported[id] = template;
        });
        return exported;
    }

    /**
     * Import templates configuration
     */
    import(templates: Record<string, UniversalTemplate>): void {
        Object.values(templates).forEach(template => {
            this.register(template);
        });
    }
}

// Create singleton instance
export const TemplateRegistry = new TemplateRegistryClass();
