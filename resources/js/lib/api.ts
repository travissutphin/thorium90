import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';

/**
 * API Client Configuration for Laravel Sanctum
 * 
 * This module provides a configured Axios instance for making API requests
 * to the Laravel backend with proper Sanctum authentication handling.
 * 
 * Features:
 * - Automatic CSRF token handling for SPA authentication
 * - Bearer token support for API token authentication
 * - Request/response interceptors for error handling
 * - TypeScript support with proper typing
 * 
 * Usage Examples:
 * 
 * // SPA Authentication (same domain)
 * await apiClient.get('/user');
 * 
 * // API Token Authentication
 * apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
 * await apiClient.get('/user');
 * 
 * // Making requests with role/permission checking
 * const response = await apiClient.get('/admin/users');
 */

// Create axios instance with base configuration
const apiClient: AxiosInstance = axios.create({
    baseURL: '/api',
    timeout: 10000,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true, // Important for SPA authentication with cookies
});

// Request interceptor to handle CSRF token for SPA authentication
apiClient.interceptors.request.use(
    (config: AxiosRequestConfig) => {
        // Get CSRF token from meta tag (set by Laravel)
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (token && config.headers) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor for error handling
apiClient.interceptors.response.use(
    (response: AxiosResponse) => {
        return response;
    },
    (error) => {
        // Handle common API errors
        if (error.response) {
            const { status, data } = error.response;
            
            switch (status) {
                case 401:
                    // Unauthorized - redirect to login or show auth error
                    console.error('Unauthorized access - please login');
                    break;
                case 403:
                    // Forbidden - insufficient permissions
                    console.error('Access forbidden - insufficient permissions');
                    break;
                case 419:
                    // CSRF token mismatch - refresh page or get new token
                    console.error('CSRF token mismatch - please refresh the page');
                    break;
                case 422:
                    // Validation errors
                    console.error('Validation errors:', data.errors);
                    break;
                case 500:
                    // Server error
                    console.error('Server error - please try again later');
                    break;
                default:
                    console.error('API Error:', error.message);
            }
        } else if (error.request) {
            // Network error
            console.error('Network error - please check your connection');
        } else {
            console.error('Request error:', error.message);
        }
        
        return Promise.reject(error);
    }
);

/**
 * API Helper Functions
 */

// Initialize CSRF protection for SPA authentication
export const initializeCsrfProtection = async (): Promise<void> => {
    try {
        await axios.get('/sanctum/csrf-cookie');
    } catch (error) {
        console.error('Failed to initialize CSRF protection:', error);
        throw error;
    }
};

// Set API token for authentication
export const setApiToken = (token: string): void => {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
};

// Remove API token
export const removeApiToken = (): void => {
    delete apiClient.defaults.headers.common['Authorization'];
};

// Get current user information
export const getCurrentUser = async () => {
    const response = await apiClient.get('/user');
    return response.data;
};

// Token management functions
export const createApiToken = async (name: string, abilities: string[] = ['*']) => {
    const response = await apiClient.post('/tokens', { name, abilities });
    return response.data;
};

export const getUserTokens = async () => {
    const response = await apiClient.get('/tokens');
    return response.data;
};

export const revokeApiToken = async (tokenId: number) => {
    const response = await apiClient.delete(`/tokens/${tokenId}`);
    return response.data;
};

// Role-based API calls
export const getAdminUsers = async () => {
    const response = await apiClient.get('/admin/users');
    return response.data;
};

export const getAdminRoles = async () => {
    const response = await apiClient.get('/admin/roles');
    return response.data;
};

export const getAdminPermissions = async () => {
    const response = await apiClient.get('/admin/permissions');
    return response.data;
};

export const getContentPosts = async () => {
    const response = await apiClient.get('/content/posts');
    return response.data;
};

export const getAuthorPosts = async () => {
    const response = await apiClient.get('/author/my-posts');
    return response.data;
};

export const getUserManagement = async () => {
    const response = await apiClient.get('/user-management');
    return response.data;
};

// Health check
export const checkApiHealth = async () => {
    const response = await apiClient.get('/health');
    return response.data;
};

export default apiClient;
