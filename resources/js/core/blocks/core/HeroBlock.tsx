import React from 'react';
import { BlockProps } from '../BlockRegistry';

export const HeroBlock: React.FC<BlockProps> = ({ 
    content, 
    config, 
    blockContent 
}) => {
    const title = blockContent?.title as string || content.title;
    const subtitle = blockContent?.subtitle as string || content.meta.description;
    const backgroundImage = config.backgroundImage as string;
    const alignment = config.alignment as string || 'center';
    const height = config.height as string || 'lg';

    const heightClasses = {
        sm: 'h-64',
        md: 'h-96',
        lg: 'h-[32rem]',
        xl: 'h-[40rem]',
        full: 'h-screen'
    };

    const alignmentClasses = {
        left: 'text-left justify-start',
        center: 'text-center justify-center',
        right: 'text-right justify-end'
    };

    return (
        <div 
            className={`hero-block relative flex items-center ${heightClasses[height as keyof typeof heightClasses] || heightClasses.lg} ${alignmentClasses[alignment as keyof typeof alignmentClasses] || alignmentClasses.center}`}
            style={backgroundImage ? {
                backgroundImage: `url(${backgroundImage})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center'
            } : undefined}
        >
            {backgroundImage && (
                <div className="absolute inset-0 bg-black bg-opacity-40"></div>
            )}
            
            <div className="container mx-auto px-4 relative z-10">
                <div className="max-w-4xl mx-auto">
                    <h1 className={`text-4xl md:text-6xl font-bold mb-6 ${backgroundImage ? 'text-white' : 'text-gray-900'}`}>
                        {title}
                    </h1>
                    {subtitle && (
                        <p className={`text-xl md:text-2xl mb-8 ${backgroundImage ? 'text-gray-200' : 'text-gray-600'}`}>
                            {subtitle}
                        </p>
                    )}
                    {config.showCTA && (
                        <div className="flex flex-wrap gap-4 justify-center">
                            <button className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors">
                                {(config.ctaText as string) || 'Get Started'}
                            </button>
                            {config.secondaryCTA && (
                                <button className="border-2 border-white text-white hover:bg-white hover:text-gray-900 px-8 py-3 rounded-lg text-lg font-semibold transition-colors">
                                    {(config.secondaryCTAText as string) || 'Learn More'}
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default HeroBlock;
