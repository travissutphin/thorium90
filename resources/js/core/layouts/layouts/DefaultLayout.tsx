import React from 'react';
import { LayoutProps } from '../LayoutRegistry';

export const DefaultLayout: React.FC<LayoutProps> = ({ 
    theme, 
    children 
}) => {
    return (
        <div className={`default-layout ${theme ? `theme-${theme}` : ''}`}>
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-4xl mx-auto">
                    {children}
                </div>
            </div>
        </div>
    );
};

export default DefaultLayout;
