import { ComponentType } from 'react';
import { ContentEntity } from '../templates/TemplateRegistry';

export interface LayoutConfig {
    name: string;
    description?: string;
    sections: string[];
    defaultSections?: Record<string, unknown>;
    settings?: Record<string, unknown>;
}

export interface LayoutProps {
    content: ContentEntity;
    theme?: string;
    config?: Record<string, unknown>;
    children: React.ReactNode;
}

export interface UniversalLayout {
    id: string;
    name: string;
    description?: string;
    plugin: string; // 'core' | 'blog' | 'ecommerce' | 'classifieds'
    category: string; // 'page' | 'post' | 'product' | 'classified'
    config: LayoutConfig;
    component: ComponentType<LayoutProps>;
    preview?: string; // Preview image URL
    isActive: boolean;
}

class LayoutRegistryClass {
    private layouts: Map<string, UniversalLayout> = new Map();
    private pluginLayouts: Map<string, UniversalLayout[]> = new Map();

    /**
     * Register a layout
     */
    register(layout: UniversalLayout): void {
        if (this.layouts.has(layout.id)) {
            console.warn(`Layout ${layout.id} is already registered. Overwriting.`);
        }

        this.layouts.set(layout.id, layout);

        // Group by plugin for easier management
        const pluginLayouts = this.pluginLayouts.get(layout.plugin) || [];
        const existingIndex = pluginLayouts.findIndex(l => l.id === layout.id);
        
        if (existingIndex >= 0) {
            pluginLayouts[existingIndex] = layout;
        } else {
            pluginLayouts.push(layout);
        }
        
        this.pluginLayouts.set(layout.plugin, pluginLayouts);
    }

    /**
     * Register multiple layouts
     */
    registerMultiple(layouts: UniversalLayout[]): void {
        layouts.forEach(layout => this.register(layout));
    }

    /**
     * Get a layout by ID
     */
    get(layoutId: string): UniversalLayout | undefined {
        return this.layouts.get(layoutId);
    }

    /**
     * Get all layouts
     */
    getAll(): UniversalLayout[] {
        return Array.from(this.layouts.values());
    }

    /**
     * Get layouts by plugin
     */
    getByPlugin(plugin: string): UniversalLayout[] {
        return this.pluginLayouts.get(plugin) || [];
    }

    /**
     * Get layouts by category
     */
    getByCategory(category: string): UniversalLayout[] {
        return Array.from(this.layouts.values()).filter(
            layout => layout.category === category
        );
    }

    /**
     * Get active layouts only
     */
    getActive(): UniversalLayout[] {
        return Array.from(this.layouts.values()).filter(
            layout => layout.isActive
        );
    }

    /**
     * Get layouts by content type
     */
    getByContentType(contentType: string): UniversalLayout[] {
        return this.getByCategory(contentType).filter(layout => layout.isActive);
    }

    /**
     * Check if a layout exists
     */
    has(layoutId: string): boolean {
        return this.layouts.has(layoutId);
    }

    /**
     * Remove a layout
     */
    unregister(layoutId: string): boolean {
        const layout = this.layouts.get(layoutId);
        if (!layout) return false;

        this.layouts.delete(layoutId);

        // Remove from plugin layouts
        const pluginLayouts = this.pluginLayouts.get(layout.plugin) || [];
        const filteredLayouts = pluginLayouts.filter(l => l.id !== layoutId);
        this.pluginLayouts.set(layout.plugin, filteredLayouts);

        return true;
    }

    /**
     * Get layout options for a select dropdown
     */
    getSelectOptions(contentType?: string): Array<{ value: string; label: string; description?: string }> {
        const layouts = contentType 
            ? this.getByContentType(contentType)
            : this.getActive();

        return layouts.map(layout => ({
            value: layout.id,
            label: layout.name,
            description: layout.description
        }));
    }

    /**
     * Get available sections for a layout
     */
    getLayoutSections(layoutId: string): string[] {
        const layout = this.get(layoutId);
        return layout?.config.sections || [];
    }

    /**
     * Validate layout configuration
     */
    validateLayout(layout: Partial<UniversalLayout>): string[] {
        const errors: string[] = [];

        if (!layout.id) errors.push('Layout ID is required');
        if (!layout.name) errors.push('Layout name is required');
        if (!layout.plugin) errors.push('Layout plugin is required');
        if (!layout.category) errors.push('Layout category is required');
        if (!layout.component) errors.push('Layout component is required');
        if (!layout.config?.sections || layout.config.sections.length === 0) {
            errors.push('Layout must have at least one section');
        }

        return errors;
    }

    /**
     * Get layout statistics
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

        all.forEach(layout => {
            byPlugin[layout.plugin] = (byPlugin[layout.plugin] || 0) + 1;
            byCategory[layout.category] = (byCategory[layout.category] || 0) + 1;
        });

        return {
            total: all.length,
            active: active.length,
            byPlugin,
            byCategory
        };
    }

    /**
     * Clear all layouts (useful for testing)
     */
    clear(): void {
        this.layouts.clear();
        this.pluginLayouts.clear();
    }

    /**
     * Export layouts configuration
     */
    export(): Record<string, UniversalLayout> {
        const exported: Record<string, UniversalLayout> = {};
        this.layouts.forEach((layout, id) => {
            exported[id] = layout;
        });
        return exported;
    }

    /**
     * Import layouts configuration
     */
    import(layouts: Record<string, UniversalLayout>): void {
        Object.values(layouts).forEach(layout => {
            this.register(layout);
        });
    }
}

// Create singleton instance
export const LayoutRegistry = new LayoutRegistryClass();
