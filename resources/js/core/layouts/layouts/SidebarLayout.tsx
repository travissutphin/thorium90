import React from 'react';
import { LayoutProps } from '../LayoutRegistry';

export const SidebarLayout: React.FC<LayoutProps> = ({ 
    theme, 
    children 
}) => {
    return (
        <div className={`sidebar-layout ${theme ? `theme-${theme}` : ''}`}>
            <div className="container mx-auto px-4 py-8">
                <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <div className="lg:col-span-3">
                        {children}
                    </div>
                    <div className="lg:col-span-1">
                        <aside className="sidebar space-y-6">
                            <div className="sidebar-section">
                                <h3 className="text-lg font-semibold mb-4">Related Content</h3>
                                <div className="space-y-2">
                                    <p className="text-sm text-gray-600">Sidebar content will be populated by blocks</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SidebarLayout;
