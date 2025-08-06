import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Shield, Key, Smartphone } from 'lucide-react';

/**
 * TwoFactorChallenge Component
 * 
 * This component handles the Two-Factor Authentication challenge during the login process
 * for the Multi-Role User Authentication system. It provides interfaces for both
 * authenticator app codes and recovery codes.
 * 
 * Key Features:
 * - Authenticator app code verification
 * - Recovery code authentication
 * - Seamless integration with existing login flow
 * - Role-based redirection after successful authentication
 * - Proper error handling and user feedback
 * 
 * Security Considerations:
 * - Rate limiting on authentication attempts
 * - Secure handling of authentication codes
 * - Recovery codes are single-use
 * - Session validation to prevent bypass
 * 
 * Integration Points:
 * - Uses existing UI components from shadcn/ui
 * - Integrates with Inertia.js for navigation
 * - Respects existing authentication middleware
 * - Compatible with role-based access control
 */
export default function TwoFactorChallenge() {
    const [code, setCode] = useState('');
    const [recoveryCode, setRecoveryCode] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [activeTab, setActiveTab] = useState('code');

    const handleSubmit = async (useRecoveryCode = false) => {
        const authCode = useRecoveryCode ? recoveryCode : code;
        
        if (!authCode.trim()) {
            setError(useRecoveryCode ? 'Please enter a recovery code.' : 'Please enter an authentication code.');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/two-factor-challenge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    code: useRecoveryCode ? null : authCode,
                    recovery_code: useRecoveryCode ? authCode : null,
                }),
            });

            if (response.ok) {
                const data = await response.json();
                // Redirect to the appropriate dashboard based on user role
                window.location.href = data.redirect_url || '/dashboard';
            } else {
                const errorData = await response.json();
                setError(errorData.error || 'Authentication failed. Please try again.');
            }
        } catch {
            setError('An error occurred during authentication. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const handleCodeSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleSubmit(false);
    };

    const handleRecoverySubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleSubmit(true);
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8">
                <div className="text-center">
                    <Shield className="mx-auto h-12 w-12 text-blue-600" />
                    <h2 className="mt-6 text-3xl font-extrabold text-gray-900">
                        Two-Factor Authentication
                    </h2>
                    <p className="mt-2 text-sm text-gray-600">
                        Please verify your identity to continue
                    </p>
                </div>

                <Card>
                    <CardContent className="p-6">
                        {error && (
                            <Alert variant="destructive" className="mb-4">
                                <AlertDescription>{error}</AlertDescription>
                            </Alert>
                        )}

                        <div className="w-full">
                            <div className="flex rounded-lg bg-gray-100 p-1 mb-4">
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('code')}
                                    className={`flex-1 flex items-center justify-center space-x-2 py-2 px-3 rounded-md text-sm font-medium transition-colors ${
                                        activeTab === 'code'
                                            ? 'bg-white text-gray-900 shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900'
                                    }`}
                                >
                                    <Smartphone className="h-4 w-4" />
                                    <span>Authenticator</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setActiveTab('recovery')}
                                    className={`flex-1 flex items-center justify-center space-x-2 py-2 px-3 rounded-md text-sm font-medium transition-colors ${
                                        activeTab === 'recovery'
                                            ? 'bg-white text-gray-900 shadow-sm'
                                            : 'text-gray-600 hover:text-gray-900'
                                    }`}
                                >
                                    <Key className="h-4 w-4" />
                                    <span>Recovery Code</span>
                                </button>
                            </div>

                            {activeTab === 'code' && (
                                <div className="space-y-4">
                                    <div className="text-center">
                                        <p className="text-sm text-gray-600 mb-4">
                                            Enter the 6-digit code from your authenticator app
                                        </p>
                                    </div>

                                    <form onSubmit={handleCodeSubmit} className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="code">Authentication Code</Label>
                                            <Input
                                                id="code"
                                                type="text"
                                                placeholder="000000"
                                                value={code}
                                                onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
                                                maxLength={6}
                                                className="text-center text-lg font-mono tracking-widest"
                                                autoComplete="one-time-code"
                                                autoFocus
                                            />
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full"
                                            disabled={loading || code.length !== 6}
                                        >
                                            {loading ? 'Verifying...' : 'Verify Code'}
                                        </Button>
                                    </form>
                                </div>
                            )}

                            {activeTab === 'recovery' && (
                                <div className="space-y-4">
                                    <div className="text-center">
                                        <p className="text-sm text-gray-600 mb-4">
                                            Enter one of your recovery codes
                                        </p>
                                    </div>

                                    <form onSubmit={handleRecoverySubmit} className="space-y-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="recovery-code">Recovery Code</Label>
                                            <Input
                                                id="recovery-code"
                                                type="text"
                                                placeholder="Enter recovery code"
                                                value={recoveryCode}
                                                onChange={(e) => setRecoveryCode(e.target.value.trim())}
                                                className="font-mono"
                                                autoComplete="one-time-code"
                                            />
                                            <p className="text-xs text-gray-500">
                                                Recovery codes are case-sensitive and can only be used once.
                                            </p>
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full"
                                            disabled={loading || !recoveryCode.trim()}
                                        >
                                            {loading ? 'Verifying...' : 'Use Recovery Code'}
                                        </Button>
                                    </form>
                                </div>
                            )}
                        </div>

                        <div className="mt-6 text-center">
                            <p className="text-xs text-gray-500">
                                Having trouble? Contact your administrator for assistance.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div className="text-center">
                    <Button
                        variant="link"
                        onClick={() => window.location.href = '/login'}
                        className="text-sm text-gray-600 hover:text-gray-900"
                    >
                        ← Back to login
                    </Button>
                </div>
            </div>
        </div>
    );
}
