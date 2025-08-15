import React from 'react';
import { LayoutProps } from '../LayoutRegistry';

export const FullWidthLayout: React.FC<LayoutProps> = ({ 
    theme, 
    children 
}) => {
    return (
        <div className={`full-width-layout ${theme ? `theme-${theme}` : ''}`}>
            <div className="w-full">
                {children}
            </div>
        </div>
    );
};

export default FullWidthLayout;
