import { useState, useEffect, useCallback } from 'react';
import { usePage } from '@inertiajs/react';
import {
    initializeCsrfProtection,
    setApiToken,
    removeApiToken,
    getCurrentUser,
    createApiToken,
    getUserTokens,
    revokeApiToken,
    checkApiHealth,
} from '../lib/api';

/**
 * Custom React Hook for API Management with Laravel Sanctum
 * 
 * This hook provides a convenient interface for managing API authentication,
 * token management, and making authenticated requests to the Laravel backend.
 * 
 * Features:
 * - Automatic CSRF protection initialization
 * - API token management (create, list, revoke)
 * - Authentication state management
 * - Error handling and loading states
 * - Integration with existing Inertia.js authentication
 * 
 * Usage:
 * ```tsx
 * const {
 *   isLoading,
 *   error,
 *   tokens,
 *   createToken,
 *   revokeToken,
 *   refreshTokens,
 *   testApiConnection
 * } = useApi();
 * ```
 */

interface ApiToken {
    id: number;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    expires_at: string | null;
    created_at: string;
}

interface ApiError {
    message: string;
    status?: number;
    errors?: Record<string, string[]>;
}

interface UseApiReturn {
    // State
    isLoading: boolean;
    error: ApiError | null;
    tokens: ApiToken[];
    isAuthenticated: boolean;
    
    // Token management
    createToken: (name: string, abilities?: string[]) => Promise<{ token: string; name: string; abilities: string[] } | null>;
    revokeToken: (tokenId: number) => Promise<boolean>;
    refreshTokens: () => Promise<void>;
    
    // Authentication
    setToken: (token: string) => void;
    clearToken: () => void;
    testApiConnection: () => Promise<boolean>;
    
    // User data
    refreshUserData: () => Promise<Record<string, unknown> | null>;
    
    // Utilities
    clearError: () => void;
}

export const useApi = (): UseApiReturn => {
    const { auth } = usePage<{ auth?: { user?: Record<string, unknown> } }>().props;
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<ApiError | null>(null);
    const [tokens, setTokens] = useState<ApiToken[]>([]);
    const [isAuthenticated, setIsAuthenticated] = useState(!!auth?.user);

    // Initialize CSRF protection on mount
    useEffect(() => {
        const initializeCsrf = async () => {
            try {
                await initializeCsrfProtection();
            } catch (err) {
                console.error('Failed to initialize CSRF protection:', err);
            }
        };

        if (auth?.user) {
            initializeCsrf();
        }
    }, [auth?.user]);

    // Clear error helper
    const clearError = useCallback(() => {
        setError(null);
    }, []);

    // Handle API errors
    const handleApiError = useCallback((err: unknown): ApiError => {
        // Type guard for axios errors
        if (err && typeof err === 'object' && 'response' in err) {
            const axiosError = err as { response: { data?: { message?: string; errors?: Record<string, string[]> }; status: number } };
            return {
                message: axiosError.response.data?.message || 'API request failed',
                status: axiosError.response.status,
                errors: axiosError.response.data?.errors,
            };
        } else if (err && typeof err === 'object' && 'request' in err) {
            return {
                message: 'Network error - please check your connection',
            };
        } else if (err && typeof err === 'object' && 'message' in err) {
            const errorWithMessage = err as { message: string };
            return {
                message: errorWithMessage.message || 'An unexpected error occurred',
            };
        } else {
            return {
                message: 'An unexpected error occurred',
            };
        }
    }, []);

    // Create new API token
    const createToken = useCallback(async (
        name: string,
        abilities: string[] = ['*']
    ): Promise<{ token: string; name: string; abilities: string[] } | null> => {
        if (!isAuthenticated) {
            setError({ message: 'User must be authenticated to create tokens' });
            return null;
        }

        setIsLoading(true);
        setError(null);

        try {
            const result = await createApiToken(name, abilities);
            await refreshTokens(); // Refresh the token list
            return result;
        } catch (err) {
            const apiError = handleApiError(err);
            setError(apiError);
            return null;
        } finally {
            setIsLoading(false);
        }
    }, [isAuthenticated, handleApiError]);

    // Revoke API token
    const revokeToken = useCallback(async (tokenId: number): Promise<boolean> => {
        if (!isAuthenticated) {
            setError({ message: 'User must be authenticated to revoke tokens' });
            return false;
        }

        setIsLoading(true);
        setError(null);

        try {
            await revokeApiToken(tokenId);
            await refreshTokens(); // Refresh the token list
            return true;
        } catch (err) {
            const apiError = handleApiError(err);
            setError(apiError);
            return false;
        } finally {
            setIsLoading(false);
        }
    }, [isAuthenticated, handleApiError]);

    // Refresh tokens list
    const refreshTokens = useCallback(async (): Promise<void> => {
        if (!isAuthenticated) {
            setTokens([]);
            return;
        }

        setIsLoading(true);
        setError(null);

        try {
            const result = await getUserTokens();
            setTokens(result.tokens || []);
        } catch (err) {
            const apiError = handleApiError(err);
            setError(apiError);
            setTokens([]);
        } finally {
            setIsLoading(false);
        }
    }, [isAuthenticated, handleApiError]);

    // Set API token for requests
    const setToken = useCallback((token: string) => {
        setApiToken(token);
        setIsAuthenticated(true);
    }, []);

    // Clear API token
    const clearToken = useCallback(() => {
        removeApiToken();
        setIsAuthenticated(!!auth?.user); // Fall back to session auth if available
    }, [auth?.user]);

    // Test API connection
    const testApiConnection = useCallback(async (): Promise<boolean> => {
        setIsLoading(true);
        setError(null);

        try {
            await checkApiHealth();
            return true;
        } catch (err) {
            const apiError = handleApiError(err);
            setError(apiError);
            return false;
        } finally {
            setIsLoading(false);
        }
    }, [handleApiError]);

    // Refresh user data from API
    const refreshUserData = useCallback(async () => {
        if (!isAuthenticated) {
            return null;
        }

        setIsLoading(true);
        setError(null);

        try {
            const userData = await getCurrentUser();
            return userData;
        } catch (err) {
            const apiError = handleApiError(err);
            setError(apiError);
            return null;
        } finally {
            setIsLoading(false);
        }
    }, [isAuthenticated, handleApiError]);

    // Load tokens on mount if authenticated
    useEffect(() => {
        if (isAuthenticated) {
            refreshTokens();
        }
    }, [isAuthenticated, refreshTokens]);

    return {
        // State
        isLoading,
        error,
        tokens,
        isAuthenticated,
        
        // Token management
        createToken,
        revokeToken,
        refreshTokens,
        
        // Authentication
        setToken,
        clearToken,
        testApiConnection,
        
        // User data
        refreshUserData,
        
        // Utilities
        clearError,
    };
};

export default useApi;
