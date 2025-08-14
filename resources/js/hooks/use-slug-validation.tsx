import { useState, useCallback } from 'react';

interface SlugValidationResult {
    available: boolean;
    message: string;
    suggestion?: string;
    formatted: string;
}

interface UseSlugValidationOptions {
    excludeId?: number;
    debounceMs?: number;
}

export function useSlugValidation(options: UseSlugValidationOptions = {}) {
    const { excludeId, debounceMs = 500 } = options;
    const [isChecking, setIsChecking] = useState(false);
    const [validationResult, setValidationResult] = useState<SlugValidationResult | null>(null);
    const [debounceTimeout, setDebounceTimeout] = useState<NodeJS.Timeout | null>(null);

    const validateSlug = useCallback(async (slug: string): Promise<SlugValidationResult> => {
        if (!slug.trim()) {
            return {
                available: false,
                message: 'Slug cannot be empty',
                formatted: ''
            };
        }

        try {
            const response = await fetch(route('content.pages.check-slug'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (window as { Laravel?: { csrfToken?: string } }).Laravel?.csrfToken || 
                                   document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    slug,
                    exclude_id: excludeId
                })
            });

            if (!response.ok) {
                if (response.status === 419) {
                    return {
                        available: false,
                        message: 'Session expired. Please refresh the page.',
                        formatted: slug
                    };
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Slug validation error:', error);
            return {
                available: false,
                message: 'Unable to validate slug. Please check manually.',
                formatted: slug
            };
        }
    }, [excludeId]);

    const checkSlug = useCallback((slug: string, callback?: (result: SlugValidationResult) => void) => {
        // Clear existing timeout
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }

        // Set new timeout
        const timeout = setTimeout(async () => {
            setIsChecking(true);
            const result = await validateSlug(slug);
            setValidationResult(result);
            setIsChecking(false);
            
            if (callback) {
                callback(result);
            }
        }, debounceMs);

        setDebounceTimeout(timeout);
    }, [validateSlug, debounceTimeout, debounceMs]);

    const generateSlug = useCallback((title: string): string => {
        return title
            .toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '')
            .trim();
    }, []);

    const clearValidation = useCallback(() => {
        setValidationResult(null);
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
            setDebounceTimeout(null);
        }
    }, [debounceTimeout]);

    return {
        isChecking,
        validationResult,
        checkSlug,
        generateSlug,
        clearValidation,
        validateSlug
    };
}
