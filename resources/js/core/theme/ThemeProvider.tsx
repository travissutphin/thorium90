import React, { createContext, useContext, ReactNode } from 'react';

export interface Theme {
    id: string;
    name: string;
    colors: {
        primary: string;
        secondary: string;
        accent: string;
        background: string;
        surface: string;
        text: string;
        textSecondary: string;
    };
    typography: {
        fontFamily: string;
        headingFontFamily?: string;
        fontSize: {
            xs: string;
            sm: string;
            base: string;
            lg: string;
            xl: string;
            '2xl': string;
            '3xl': string;
            '4xl': string;
        };
    };
    spacing: {
        xs: string;
        sm: string;
        md: string;
        lg: string;
        xl: string;
    };
    borderRadius: {
        sm: string;
        md: string;
        lg: string;
        xl: string;
    };
    shadows: {
        sm: string;
        md: string;
        lg: string;
        xl: string;
    };
}

interface ThemeContextType {
    theme: Theme | null;
    setTheme: (theme: Theme | null) => void;
}

const ThemeContext = createContext<ThemeContextType>({
    theme: null,
    setTheme: () => {}
});

interface ThemeProviderProps {
    theme?: string | Theme;
    children: ReactNode;
}

// Default theme
const defaultTheme: Theme = {
    id: 'default',
    name: 'Default',
    colors: {
        primary: '#3b82f6',
        secondary: '#6b7280',
        accent: '#10b981',
        background: '#ffffff',
        surface: '#f9fafb',
        text: '#111827',
        textSecondary: '#6b7280'
    },
    typography: {
        fontFamily: 'Inter, system-ui, sans-serif',
        headingFontFamily: 'Inter, system-ui, sans-serif',
        fontSize: {
            xs: '0.75rem',
            sm: '0.875rem',
            base: '1rem',
            lg: '1.125rem',
            xl: '1.25rem',
            '2xl': '1.5rem',
            '3xl': '1.875rem',
            '4xl': '2.25rem'
        }
    },
    spacing: {
        xs: '0.5rem',
        sm: '1rem',
        md: '1.5rem',
        lg: '2rem',
        xl: '3rem'
    },
    borderRadius: {
        sm: '0.25rem',
        md: '0.375rem',
        lg: '0.5rem',
        xl: '0.75rem'
    },
    shadows: {
        sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
        md: '0 4px 6px -1px rgb(0 0 0 / 0.1)',
        lg: '0 10px 15px -3px rgb(0 0 0 / 0.1)',
        xl: '0 20px 25px -5px rgb(0 0 0 / 0.1)'
    }
};

export const ThemeProvider: React.FC<ThemeProviderProps> = ({ 
    theme: themeProp, 
    children 
}) => {
    const [currentTheme, setCurrentTheme] = React.useState<Theme | null>(null);

    React.useEffect(() => {
        if (typeof themeProp === 'string') {
            // In a real implementation, you would load the theme from a registry
            // For now, we'll use the default theme
            setCurrentTheme(defaultTheme);
        } else if (themeProp) {
            setCurrentTheme(themeProp);
        } else {
            setCurrentTheme(defaultTheme);
        }
    }, [themeProp]);

    const contextValue: ThemeContextType = {
        theme: currentTheme,
        setTheme: setCurrentTheme
    };

    return (
        <ThemeContext.Provider value={contextValue}>
            <div 
                className="theme-provider"
                style={currentTheme ? {
                    '--theme-primary': currentTheme.colors.primary,
                    '--theme-secondary': currentTheme.colors.secondary,
                    '--theme-accent': currentTheme.colors.accent,
                    '--theme-background': currentTheme.colors.background,
                    '--theme-surface': currentTheme.colors.surface,
                    '--theme-text': currentTheme.colors.text,
                    '--theme-text-secondary': currentTheme.colors.textSecondary,
                    '--theme-font-family': currentTheme.typography.fontFamily,
                    '--theme-heading-font-family': currentTheme.typography.headingFontFamily || currentTheme.typography.fontFamily
                } as React.CSSProperties : undefined}
                data-theme={currentTheme?.id}
            >
                {children}
            </div>
        </ThemeContext.Provider>
    );
};

export const useTheme = (): ThemeContextType => {
    const context = useContext(ThemeContext);
    if (!context) {
        throw new Error('useTheme must be used within a ThemeProvider');
    }
    return context;
};

export default ThemeProvider;
