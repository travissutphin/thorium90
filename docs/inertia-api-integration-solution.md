# Long-Term Solution: Inertia.js API Integration Architecture

## Problem Summary

Inertia.js intercepts fetch requests made from React components, expecting Inertia responses instead of JSON. This breaks API calls that should return plain JSON data.

## Root Cause Analysis

1. **Framework Mismatch**: Inertia.js assumes all requests should return Inertia responses
2. **Header Inconsistency**: Different requests use different header patterns
3. **Component Isolation**: API-calling components don't follow consistent patterns
4. **Mixed Paradigms**: Using both Inertia forms and raw fetch calls inconsistently

## Comprehensive Solution

### 1. Standardized API Client

Create a centralized API client that handles all backend communication:

```typescript
// resources/js/lib/api-client.ts
class ApiClient {
    private static baseHeaders = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    private static getAuthHeaders(): Record<string, string> {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        return csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {};
    }

    static async get(url: string): Promise<Response> {
        return fetch(url, {
            method: 'GET',
            headers: {
                ...this.baseHeaders,
                ...this.getAuthHeaders(),
            },
        });
    }

    static async post(url: string, data?: any): Promise<Response> {
        return fetch(url, {
            method: 'POST',
            headers: {
                ...this.baseHeaders,
                ...this.getAuthHeaders(),
            },
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    static async put(url: string, data?: any): Promise<Response> {
        return fetch(url, {
            method: 'PUT',
            headers: {
                ...this.baseHeaders,
                ...this.getAuthHeaders(),
            },
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    static async delete(url: string): Promise<Response> {
        return fetch(url, {
            method: 'DELETE',
            headers: {
                ...this.baseHeaders,
                ...this.getAuthHeaders(),
            },
        });
    }
}

export default ApiClient;
```

### 2. API Response Hooks

Create custom hooks that handle API state management:

```typescript
// resources/js/hooks/use-api.ts
import { useState, useEffect } from 'react';
import ApiClient from '@/lib/api-client';

export function useApi<T>(url: string, options?: { immediate?: boolean }) {
    const [data, setData] = useState<T | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const execute = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await ApiClient.get(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            setData(result);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (options?.immediate !== false) {
            execute();
        }
    }, [url]);

    return { data, loading, error, refetch: execute };
}
```

### 3. Two-Factor Authentication Hook

Create a specialized hook for 2FA management:

```typescript
// resources/js/hooks/use-two-factor.ts
import { useState } from 'react';
import ApiClient from '@/lib/api-client';
import { useApi } from './use-api';

interface TwoFactorStatus {
    two_factor_enabled: boolean;
    two_factor_confirmed: boolean;
    recovery_codes_count: number;
}

export function useTwoFactor() {
    const { data: status, loading, error, refetch } = useApi<TwoFactorStatus>('/user/two-factor-authentication');
    const [actionLoading, setActionLoading] = useState(false);

    const enable2FA = async () => {
        setActionLoading(true);
        try {
            const response = await ApiClient.post('/user/two-factor-authentication');
            if (response.ok) {
                await refetch();
                return await response.json();
            }
            throw new Error('Failed to enable 2FA');
        } finally {
            setActionLoading(false);
        }
    };

    const disable2FA = async () => {
        setActionLoading(true);
        try {
            const response = await ApiClient.delete('/user/two-factor-authentication');
            if (response.ok) {
                await refetch();
                return await response.json();
            }
            throw new Error('Failed to disable 2FA');
        } finally {
            setActionLoading(false);
        }
    };

    const confirm2FA = async (code: string) => {
        setActionLoading(true);
        try {
            const response = await ApiClient.post('/user/two-factor-authentication/confirm', { code });
            if (response.ok) {
                await refetch();
                return await response.json();
            }
            throw new Error('Failed to confirm 2FA');
        } finally {
            setActionLoading(false);
        }
    };

    return {
        status,
        loading: loading || actionLoading,
        error,
        enable2FA,
        disable2FA,
        confirm2FA,
        refetch,
    };
}
```

### 4. Route-Based API Definitions

Define API routes centrally to avoid hardcoded URLs:

```typescript
// resources/js/lib/api-routes.ts
export const API_ROUTES = {
    TWO_FACTOR: {
        STATUS: '/user/two-factor-authentication',
        ENABLE: '/user/two-factor-authentication',
        DISABLE: '/user/two-factor-authentication',
        CONFIRM: '/user/two-factor-authentication/confirm',
        QR_CODE: '/user/two-factor-authentication/qr-code',
        RECOVERY_CODES: '/user/two-factor-authentication/recovery-codes',
    },
    USER: {
        PROFILE: '/user/profile-information',
    },
} as const;
```

### 5. Updated TwoFactorAuthentication Component

Refactor the component to use the new architecture:

```typescript
// resources/js/components/TwoFactorAuthentication.tsx
import React from 'react';
import { useTwoFactor } from '@/hooks/use-two-factor';

export default function TwoFactorAuthentication() {
    const { status, loading, error, enable2FA, disable2FA, confirm2FA } = useTwoFactor();

    if (loading && !status) {
        return <div>Loading...</div>;
    }

    if (error) {
        return <div>Error: {error}</div>;
    }

    // Component renders status-based UI
    // All API calls now use the centralized system
}
```

### 6. Clear Separation of Concerns

**Inertia.js for Navigation & Forms:**
- Page navigation
- Form submissions that result in redirects
- Traditional CRUD operations

**API Client for AJAX:**
- Status checks
- Real-time updates
- Dynamic content loading
- File uploads

### 7. Global Error Handling

Add global error handling for API responses:

```typescript
// resources/js/lib/error-handler.ts
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

export function handleApiError(error: any): never {
    if (error instanceof ApiError) {
        // Handle API-specific errors
        console.error('API Error:', error.message, error.status);
    } else {
        // Handle network/other errors
        console.error('Network Error:', error.message);
    }
    
    throw error;
}
```

## Implementation Plan

### Phase 1: Infrastructure
1. Create `ApiClient` class
2. Create `useApi` hook
3. Create `API_ROUTES` constants

### Phase 2: Component Migration
1. Update `TwoFactorAuthentication` component
2. Create `useTwoFactor` hook
3. Test all 2FA functionality

### Phase 3: Global Integration
1. Add global error handling
2. Update other components using similar patterns
3. Create development tools for API debugging

### Phase 4: Documentation & Testing
1. Document the new patterns
2. Create comprehensive tests
3. Add browser tests for integration scenarios

## Benefits

1. **Consistent API Communication**: All components use the same client
2. **Proper Header Management**: Headers are automatically set correctly
3. **Error Handling**: Centralized error handling and logging
4. **Type Safety**: Full TypeScript support for API responses
5. **Testing**: Easier to mock and test API interactions
6. **Maintainability**: Clear separation between Inertia and API concerns

## Migration Strategy

1. **Gradual Migration**: Update components one at a time
2. **Backward Compatibility**: Keep existing patterns until migration is complete
3. **Testing**: Comprehensive testing at each step
4. **Documentation**: Clear examples and patterns for developers