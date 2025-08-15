import React from 'react';
import { BlockProps } from '../BlockRegistry';

export const ContentBlock: React.FC<BlockProps> = ({ 
    content, 
    config, 
    blockContent 
}) => {
    const showTitle = config.showTitle !== false;
    const showMeta = config.showMeta !== false;
    const contentToRender = blockContent?.content as string || content.content;

    return (
        <div className="content-block">
            {showTitle && (
                <header className="content-header mb-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-4">
                        {content.title}
                    </h1>
                    {showMeta && (
                        <div className="content-meta flex items-center gap-4 text-sm text-gray-600 mb-4">
                            {content.user && (
                                <span>By {content.user.name}</span>
                            )}
                            {content.published_at && (
                                <span>
                                    Published {new Date(content.published_at).toLocaleDateString()}
                                </span>
                            )}
                        </div>
                    )}
                </header>
            )}
            
            <div className="content-body prose prose-lg max-w-none">
                {typeof contentToRender === 'string' ? (
                    <div dangerouslySetInnerHTML={{ __html: contentToRender }} />
                ) : (
                    <pre className="bg-gray-100 p-4 rounded text-sm overflow-auto">
                        {JSON.stringify(contentToRender, null, 2)}
                    </pre>
                )}
            </div>
        </div>
    );
};

export default ContentBlock;
