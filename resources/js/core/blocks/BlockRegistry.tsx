import { ComponentType } from 'react';
import { ContentEntity } from '../templates/TemplateRegistry';

export interface BlockConfig {
    type: string;
    position: number;
    config: Record<string, unknown>;
    content?: Record<string, unknown>;
}

export interface BlockProps {
    content: ContentEntity;
    config: Record<string, unknown>;
    blockContent?: Record<string, unknown>;
    position?: number;
}

export interface UniversalBlock {
    id: string;
    name: string;
    description?: string;
    plugin: string; // 'core' | 'blog' | 'ecommerce' | 'classifieds'
    category: string; // 'content' | 'navigation' | 'sidebar' | 'footer' | 'hero'
    component: ComponentType<BlockProps>;
    defaultConfig?: Record<string, unknown>;
    configSchema?: Record<string, unknown>; // JSON schema for configuration
    preview?: string; // Preview image URL
    isActive: boolean;
}

class BlockRegistryClass {
    private blocks: Map<string, UniversalBlock> = new Map();
    private pluginBlocks: Map<string, UniversalBlock[]> = new Map();
    private categoryBlocks: Map<string, UniversalBlock[]> = new Map();

    /**
     * Register a block
     */
    register(block: UniversalBlock): void {
        if (this.blocks.has(block.id)) {
            console.warn(`Block ${block.id} is already registered. Overwriting.`);
        }

        this.blocks.set(block.id, block);

        // Group by plugin
        const pluginBlocks = this.pluginBlocks.get(block.plugin) || [];
        const existingPluginIndex = pluginBlocks.findIndex(b => b.id === block.id);
        
        if (existingPluginIndex >= 0) {
            pluginBlocks[existingPluginIndex] = block;
        } else {
            pluginBlocks.push(block);
        }
        
        this.pluginBlocks.set(block.plugin, pluginBlocks);

        // Group by category
        const categoryBlocks = this.categoryBlocks.get(block.category) || [];
        const existingCategoryIndex = categoryBlocks.findIndex(b => b.id === block.id);
        
        if (existingCategoryIndex >= 0) {
            categoryBlocks[existingCategoryIndex] = block;
        } else {
            categoryBlocks.push(block);
        }
        
        this.categoryBlocks.set(block.category, categoryBlocks);
    }

    /**
     * Register multiple blocks
     */
    registerMultiple(blocks: UniversalBlock[]): void {
        blocks.forEach(block => this.register(block));
    }

    /**
     * Get a block by ID
     */
    get(blockId: string): UniversalBlock | undefined {
        return this.blocks.get(blockId);
    }

    /**
     * Get all blocks
     */
    getAll(): UniversalBlock[] {
        return Array.from(this.blocks.values());
    }

    /**
     * Get blocks by plugin
     */
    getByPlugin(plugin: string): UniversalBlock[] {
        return this.pluginBlocks.get(plugin) || [];
    }

    /**
     * Get blocks by category
     */
    getByCategory(category: string): UniversalBlock[] {
        return this.categoryBlocks.get(category) || [];
    }

    /**
     * Get active blocks only
     */
    getActive(): UniversalBlock[] {
        return Array.from(this.blocks.values()).filter(
            block => block.isActive
        );
    }

    /**
     * Get active blocks by category
     */
    getActiveByCategory(category: string): UniversalBlock[] {
        return this.getByCategory(category).filter(block => block.isActive);
    }

    /**
     * Check if a block exists
     */
    has(blockId: string): boolean {
        return this.blocks.has(blockId);
    }

    /**
     * Remove a block
     */
    unregister(blockId: string): boolean {
        const block = this.blocks.get(blockId);
        if (!block) return false;

        this.blocks.delete(blockId);

        // Remove from plugin blocks
        const pluginBlocks = this.pluginBlocks.get(block.plugin) || [];
        const filteredPluginBlocks = pluginBlocks.filter(b => b.id !== blockId);
        this.pluginBlocks.set(block.plugin, filteredPluginBlocks);

        // Remove from category blocks
        const categoryBlocks = this.categoryBlocks.get(block.category) || [];
        const filteredCategoryBlocks = categoryBlocks.filter(b => b.id !== blockId);
        this.categoryBlocks.set(block.category, filteredCategoryBlocks);

        return true;
    }

    /**
     * Get block options for a select dropdown
     */
    getSelectOptions(category?: string): Array<{ value: string; label: string; description?: string; category: string }> {
        const blocks = category 
            ? this.getActiveByCategory(category)
            : this.getActive();

        return blocks.map(block => ({
            value: block.id,
            label: block.name,
            description: block.description,
            category: block.category
        }));
    }

    /**
     * Get blocks grouped by category for UI
     */
    getGroupedByCategory(): Record<string, UniversalBlock[]> {
        const grouped: Record<string, UniversalBlock[]> = {};
        
        this.getActive().forEach(block => {
            if (!grouped[block.category]) {
                grouped[block.category] = [];
            }
            grouped[block.category].push(block);
        });

        return grouped;
    }

    /**
     * Validate block configuration
     */
    validateBlock(block: Partial<UniversalBlock>): string[] {
        const errors: string[] = [];

        if (!block.id) errors.push('Block ID is required');
        if (!block.name) errors.push('Block name is required');
        if (!block.plugin) errors.push('Block plugin is required');
        if (!block.category) errors.push('Block category is required');
        if (!block.component) errors.push('Block component is required');

        return errors;
    }

    /**
     * Get block statistics
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

        all.forEach(block => {
            byPlugin[block.plugin] = (byPlugin[block.plugin] || 0) + 1;
            byCategory[block.category] = (byCategory[block.category] || 0) + 1;
        });

        return {
            total: all.length,
            active: active.length,
            byPlugin,
            byCategory
        };
    }

    /**
     * Get available categories
     */
    getCategories(): string[] {
        return Array.from(this.categoryBlocks.keys());
    }

    /**
     * Clear all blocks (useful for testing)
     */
    clear(): void {
        this.blocks.clear();
        this.pluginBlocks.clear();
        this.categoryBlocks.clear();
    }

    /**
     * Export blocks configuration
     */
    export(): Record<string, UniversalBlock> {
        const exported: Record<string, UniversalBlock> = {};
        this.blocks.forEach((block, id) => {
            exported[id] = block;
        });
        return exported;
    }

    /**
     * Import blocks configuration
     */
    import(blocks: Record<string, UniversalBlock>): void {
        Object.values(blocks).forEach(block => {
            this.register(block);
        });
    }
}

// Create singleton instance
export const BlockRegistry = new BlockRegistryClass();
