/**
 * Standardized API Client for Laravel Backend Communication
 * 
 * This client ensures that all API calls have proper headers to prevent
 * Inertia.js interception and provide consistent error handling.
 */

export interface ApiResponse<T = any> {
    data: T;
    success: boolean;
    message?: string;
}

export class ApiError extends Error {
    constructor(
        message: string,
        public status: number,
        public response?: any
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

class ApiClient {
    /**
     * Base headers that prevent Inertia.js interception
     */
    private static readonly baseHeaders = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    /**
     * Get authentication headers including CSRF token
     */
    private static getAuthHeaders(): Record<string, string> {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {};
    }

    /**
     * Get combined headers for all requests
     */
    private static getHeaders(): Record<string, string> {
        return {
            ...this.baseHeaders,
            ...this.getAuthHeaders(),
        };
    }

    /**
     * Handle API response and errors
     */
    private static async handleResponse<T>(response: Response): Promise<T> {
        if (!response.ok) {
            let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
            let errorData;
            
            try {
                errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch {
                // Response is not JSON, use status text
            }
            
            throw new ApiError(errorMessage, response.status, errorData);
        }

        return response.json();
    }

    /**
     * GET request
     */
    static async get<T = any>(url: string): Promise<T> {
        const response = await fetch(url, {
            method: 'GET',
            headers: this.getHeaders(),
        });

        return this.handleResponse<T>(response);
    }

    /**
     * POST request
     */
    static async post<T = any>(url: string, data?: any): Promise<T> {
        const response = await fetch(url, {
            method: 'POST',
            headers: this.getHeaders(),
            body: data ? JSON.stringify(data) : undefined,
        });

        return this.handleResponse<T>(response);
    }

    /**
     * PUT request
     */
    static async put<T = any>(url: string, data?: any): Promise<T> {
        const response = await fetch(url, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: data ? JSON.stringify(data) : undefined,
        });

        return this.handleResponse<T>(response);
    }

    /**
     * DELETE request
     */
    static async delete<T = any>(url: string): Promise<T> {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: this.getHeaders(),
        });

        return this.handleResponse<T>(response);
    }
}

export default ApiClient;