interface MinimalHeaderProps {
    page: {
        title: string;
        excerpt?: string;
    };
    titleSize?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';
    alignment?: 'left' | 'center' | 'right';
}

export function MinimalHeader({
    page,
    titleSize = 'xl',
    alignment = 'left',
}: MinimalHeaderProps) {
    const getTitleSizeClass = () => {
        switch (titleSize) {
            case 'sm': return 'text-xl';
            case 'md': return 'text-2xl';
            case 'lg': return 'text-3xl';
            case 'xl': return 'text-4xl';
            case '2xl': return 'text-5xl';
            case '3xl': return 'text-6xl';
            case '4xl': return 'text-7xl';
            default: return 'text-4xl';
        }
    };

    const getAlignmentClass = () => {
        switch (alignment) {
            case 'center': return 'text-center';
            case 'right': return 'text-right';
            default: return 'text-left';
        }
    };

    return (
        <header className="py-8">
            <div className="container mx-auto px-4">
                <div className={`max-w-4xl ${alignment === 'center' ? 'mx-auto' : ''} ${getAlignmentClass()}`}>
                    <h1 className={`${getTitleSizeClass()} font-bold leading-tight mb-4`}>
                        {page.title}
                    </h1>
                    
                    {page.excerpt && (
                        <p className="text-lg text-muted-foreground">
                            {page.excerpt}
                        </p>
                    )}
                </div>
            </div>
        </header>
    );
}
