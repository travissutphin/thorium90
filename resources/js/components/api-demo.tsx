import React, { useState } from 'react';
import { useApi } from '../hooks/use-api';

/**
 * API Demo Component
 * 
 * This component demonstrates the Laravel Sanctum API integration
 * with role-based access control. It provides a simple interface
 * to test API functionality including token management and
 * authenticated requests.
 */

export const ApiDemo: React.FC = () => {
    const {
        isLoading,
        error,
        tokens,
        createToken,
        revokeToken,
        testApiConnection,
        refreshUserData,
        clearError,
    } = useApi();

    const [tokenName, setTokenName] = useState('');
    const [newToken, setNewToken] = useState<string | null>(null);
    const [apiResponse, setApiResponse] = useState<string | null>(null);

    const handleCreateToken = async () => {
        if (!tokenName.trim()) {
            alert('Please enter a token name');
            return;
        }

        const result = await createToken(tokenName.trim());
        if (result) {
            setNewToken(result.token);
            setTokenName('');
            alert('Token created successfully! Copy it now - it won\'t be shown again.');
        }
    };

    const handleRevokeToken = async (tokenId: number) => {
        if (confirm('Are you sure you want to revoke this token?')) {
            await revokeToken(tokenId);
        }
    };

    const handleTestConnection = async () => {
        const success = await testApiConnection();
        setApiResponse(success ? 'API connection successful!' : 'API connection failed');
    };

    const handleRefreshUserData = async () => {
        const userData = await refreshUserData();
        setApiResponse(userData ? JSON.stringify(userData, null, 2) : 'Failed to fetch user data');
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text);
        alert('Copied to clipboard!');
    };

    return (
        <div className="max-w-4xl mx-auto p-6 space-y-6">
            <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-2xl font-bold mb-4">Laravel Sanctum API Demo</h2>
                <p className="text-gray-600 mb-6">
                    This demo shows the integration between your React frontend and Laravel Sanctum API
                    with role-based access control.
                </p>

                {/* Error Display */}
                {error && (
                    <div className="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                        <div className="flex justify-between items-start">
                            <div>
                                <h3 className="text-red-800 font-medium">Error</h3>
                                <p className="text-red-700 mt-1">{error.message}</p>
                                {error.status && (
                                    <p className="text-red-600 text-sm mt-1">Status: {error.status}</p>
                                )}
                            </div>
                            <button
                                onClick={clearError}
                                className="text-red-400 hover:text-red-600"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                )}

                {/* Loading Indicator */}
                {isLoading && (
                    <div className="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                        <p className="text-blue-700">Loading...</p>
                    </div>
                )}

                {/* API Connection Test */}
                <div className="border rounded-lg p-4 mb-6">
                    <h3 className="text-lg font-semibold mb-3">API Connection Test</h3>
                    <div className="flex gap-3 mb-3">
                        <button
                            onClick={handleTestConnection}
                            disabled={isLoading}
                            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                        >
                            Test API Health
                        </button>
                        <button
                            onClick={handleRefreshUserData}
                            disabled={isLoading}
                            className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 disabled:opacity-50"
                        >
                            Get User Data
                        </button>
                    </div>
                    {apiResponse && (
                        <div className="bg-gray-50 border rounded p-3">
                            <pre className="text-sm overflow-x-auto">{apiResponse}</pre>
                        </div>
                    )}
                </div>

                {/* Token Management */}
                <div className="border rounded-lg p-4 mb-6">
                    <h3 className="text-lg font-semibold mb-3">API Token Management</h3>
                    
                    {/* Create Token */}
                    <div className="mb-4">
                        <h4 className="font-medium mb-2">Create New Token</h4>
                        <div className="flex gap-3">
                            <input
                                type="text"
                                value={tokenName}
                                onChange={(e) => setTokenName(e.target.value)}
                                placeholder="Enter token name"
                                className="flex-1 border border-gray-300 rounded px-3 py-2"
                            />
                            <button
                                onClick={handleCreateToken}
                                disabled={isLoading || !tokenName.trim()}
                                className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                            >
                                Create Token
                            </button>
                        </div>
                    </div>

                    {/* New Token Display */}
                    {newToken && (
                        <div className="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                            <h4 className="font-medium text-yellow-800 mb-2">New Token Created</h4>
                            <p className="text-yellow-700 text-sm mb-2">
                                Copy this token now - it won't be shown again!
                            </p>
                            <div className="flex gap-2">
                                <code className="flex-1 bg-white border rounded px-3 py-2 text-sm break-all">
                                    {newToken}
                                </code>
                                <button
                                    onClick={() => copyToClipboard(newToken)}
                                    className="bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600"
                                >
                                    Copy
                                </button>
                            </div>
                            <button
                                onClick={() => setNewToken(null)}
                                className="mt-2 text-yellow-600 hover:text-yellow-800 text-sm"
                            >
                                Dismiss
                            </button>
                        </div>
                    )}

                    {/* Existing Tokens */}
                    <div>
                        <h4 className="font-medium mb-2">Existing Tokens ({tokens.length})</h4>
                        {tokens.length === 0 ? (
                            <p className="text-gray-500 text-sm">No tokens found</p>
                        ) : (
                            <div className="space-y-2">
                                {tokens.map((token) => (
                                    <div key={token.id} className="flex items-center justify-between bg-gray-50 border rounded p-3">
                                        <div>
                                            <p className="font-medium">{token.name}</p>
                                            <p className="text-sm text-gray-600">
                                                Created: {new Date(token.created_at).toLocaleDateString()}
                                            </p>
                                            {token.last_used_at && (
                                                <p className="text-sm text-gray-600">
                                                    Last used: {new Date(token.last_used_at).toLocaleDateString()}
                                                </p>
                                            )}
                                            <p className="text-sm text-gray-600">
                                                Abilities: {token.abilities.join(', ')}
                                            </p>
                                        </div>
                                        <button
                                            onClick={() => handleRevokeToken(token.id)}
                                            disabled={isLoading}
                                            className="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 disabled:opacity-50"
                                        >
                                            Revoke
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Usage Instructions */}
                <div className="border rounded-lg p-4">
                    <h3 className="text-lg font-semibold mb-3">Usage Instructions</h3>
                    <div className="space-y-2 text-sm text-gray-600">
                        <p><strong>1. Test API Connection:</strong> Click "Test API Health" to verify the API is working.</p>
                        <p><strong>2. Get User Data:</strong> Click "Get User Data" to fetch your user information via API.</p>
                        <p><strong>3. Create Token:</strong> Enter a name and create an API token for external use.</p>
                        <p><strong>4. Use Token:</strong> Copy the token and use it in API requests with the Authorization header:</p>
                        <code className="block bg-gray-100 p-2 rounded mt-1">
                            Authorization: Bearer YOUR_TOKEN_HERE
                        </code>
                        <p><strong>5. Revoke Token:</strong> Remove tokens you no longer need.</p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ApiDemo;
