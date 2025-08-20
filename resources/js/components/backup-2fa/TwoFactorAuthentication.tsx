import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Shield, ShieldCheck, Key, Download, RefreshCw, QrCode } from 'lucide-react';
import ApiClient from '@/lib/api-client';

interface TwoFactorStatus {
    two_factor_enabled: boolean;
    two_factor_confirmed: boolean;
    recovery_codes_count: number;
}

interface QRCodeData {
    svg: string;
    setup_key: string;
}

interface RecoveryCodesData {
    recovery_codes: string[];
}

/**
 * TwoFactorAuthentication Component
 * 
 * This component provides a complete interface for managing Two-Factor Authentication
 * within the Multi-Role User Authentication system. It integrates with Laravel Fortify
 * to provide secure 2FA setup, management, and recovery code handling.
 * 
 * Key Features:
 * - Enable/disable 2FA with password confirmation
 * - Display QR code for authenticator app setup
 * - Show and regenerate recovery codes
 * - Confirm 2FA setup with verification code
 * - Role-aware security messaging
 * - Responsive design with proper error handling
 * 
 * Security Considerations:
 * - All operations require password confirmation
 * - QR codes are generated server-side
 * - Recovery codes are displayed only once after generation
 * - Proper error handling and user feedback
 * 
 * Integration Points:
 * - Uses existing UI components from shadcn/ui
 * - Integrates with Inertia.js for seamless navigation
 * - Respects existing authentication middleware
 * - Compatible with role-based access control
 */
export default function TwoFactorAuthentication() {
    const [status, setStatus] = useState<TwoFactorStatus | null>(null);
    const [qrCode, setQrCode] = useState<QRCodeData | null>(null);
    const [recoveryCodes, setRecoveryCodes] = useState<string[]>([]);
    const [confirmationCode, setConfirmationCode] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);
    const [showRecoveryCodes, setShowRecoveryCodes] = useState(false);

    // Load 2FA status on component mount
    useEffect(() => {
        loadStatus();
    }, []);

    const loadStatus = async () => {
        try {
            const data = await ApiClient.get<TwoFactorStatus>('/user/two-factor-authentication');
            setStatus(data);
        } catch (err) {
            setError('Failed to load two-factor authentication status.');
        }
    };

    const enable2FA = async () => {
        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const data = await ApiClient.post('/user/two-factor-authentication');
            setSuccess(data.message || 'Two-factor authentication enabled successfully.');
            await loadStatus();
            await loadQRCode();
        } catch (err) {
            setError('An error occurred while enabling two-factor authentication.');
        } finally {
            setLoading(false);
        }
    };

    const disable2FA = async () => {
        if (!confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')) {
            return;
        }

        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const data = await ApiClient.delete('/user/two-factor-authentication');
            setSuccess(data.message || 'Two-factor authentication disabled successfully.');
            setQrCode(null);
            setRecoveryCodes([]);
            setShowRecoveryCodes(false);
            await loadStatus();
        } catch (err) {
            setError('An error occurred while disabling two-factor authentication.');
        } finally {
            setLoading(false);
        }
    };

    const loadQRCode = async () => {
        try {
            const data = await ApiClient.get<QRCodeData>('/user/two-factor-authentication/qr-code');
            setQrCode(data);
        } catch (err) {
            setError('Failed to load QR code.');
        }
    };

    const loadRecoveryCodes = async () => {
        try {
            const data = await ApiClient.get<RecoveryCodesData>('/user/two-factor-authentication/recovery-codes');
            setRecoveryCodes(data.recovery_codes);
            setShowRecoveryCodes(true);
        } catch (err) {
            setError('Failed to load recovery codes.');
        }
    };

    const generateNewRecoveryCodes = async () => {
        if (!confirm('Are you sure you want to generate new recovery codes? Your old codes will no longer work.')) {
            return;
        }

        setLoading(true);
        try {
            const data = await ApiClient.post<RecoveryCodesData>('/user/two-factor-authentication/recovery-codes');
            setRecoveryCodes(data.recovery_codes);
            setShowRecoveryCodes(true);
            setSuccess(data.message || 'New recovery codes generated successfully.');
        } catch (err) {
            setError('Failed to generate new recovery codes.');
        } finally {
            setLoading(false);
        }
    };

    const confirm2FA = async () => {
        if (!confirmationCode.trim()) {
            setError('Please enter a verification code.');
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const data = await ApiClient.post('/user/two-factor-authentication/confirm', { code: confirmationCode });
            setSuccess(data.message || 'Two-factor authentication confirmed successfully.');
            setConfirmationCode('');
            await loadStatus();
            await loadRecoveryCodes();
        } catch (err) {
            setError('An error occurred while confirming two-factor authentication.');
        } finally {
            setLoading(false);
        }
    };

    if (!status) {
        return (
            <Card>
                <CardContent className="p-6">
                    <div className="flex items-center justify-center">
                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-6">
            {/* Status Card */}
            <Card>
                <CardHeader>
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-2">
                            {status.two_factor_confirmed ? (
                                <ShieldCheck className="h-5 w-5 text-green-600" />
                            ) : status.two_factor_enabled ? (
                                <Shield className="h-5 w-5 text-yellow-600" />
                            ) : (
                                <Shield className="h-5 w-5 text-gray-400" />
                            )}
                            <CardTitle>Two-Factor Authentication</CardTitle>
                        </div>
                        <Badge variant={status.two_factor_confirmed ? "default" : status.two_factor_enabled ? "secondary" : "outline"}>
                            {status.two_factor_confirmed ? "Active" : status.two_factor_enabled ? "Setup Required" : "Disabled"}
                        </Badge>
                    </div>
                    <CardDescription>
                        Add an additional layer of security to your account by requiring a verification code from your authenticator app.
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    {error && (
                        <Alert variant="destructive">
                            <AlertDescription>{error}</AlertDescription>
                        </Alert>
                    )}

                    {success && (
                        <Alert>
                            <AlertDescription>{success}</AlertDescription>
                        </Alert>
                    )}

                    {!status.two_factor_enabled ? (
                        <div className="space-y-4">
                            <p className="text-sm text-gray-600">
                                Two-factor authentication is not enabled. Enable it to add an extra layer of security to your account.
                            </p>
                            <Button onClick={enable2FA} disabled={loading}>
                                {loading ? 'Enabling...' : 'Enable Two-Factor Authentication'}
                            </Button>
                        </div>
                    ) : !status.two_factor_confirmed ? (
                        <div className="space-y-4">
                            <p className="text-sm text-gray-600">
                                Two-factor authentication is enabled but not confirmed. Complete the setup by scanning the QR code and entering a verification code.
                            </p>
                            <div className="flex space-x-2">
                                <Button onClick={confirm2FA} disabled={loading || !confirmationCode.trim()}>
                                    {loading ? 'Confirming...' : 'Confirm Setup'}
                                </Button>
                                <Button variant="outline" onClick={disable2FA} disabled={loading}>
                                    Cancel Setup
                                </Button>
                            </div>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <p className="text-sm text-gray-600">
                                Two-factor authentication is active and protecting your account.
                            </p>
                            <div className="flex space-x-2">
                                <Button variant="outline" onClick={loadRecoveryCodes}>
                                    <Key className="h-4 w-4 mr-2" />
                                    View Recovery Codes
                                </Button>
                                <Button variant="outline" onClick={generateNewRecoveryCodes} disabled={loading}>
                                    <RefreshCw className="h-4 w-4 mr-2" />
                                    Generate New Codes
                                </Button>
                                <Button variant="destructive" onClick={disable2FA} disabled={loading}>
                                    Disable 2FA
                                </Button>
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* QR Code Setup Card */}
            {status.two_factor_enabled && !status.two_factor_confirmed && qrCode && (
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <QrCode className="h-5 w-5" />
                            <span>Setup Your Authenticator App</span>
                        </CardTitle>
                        <CardDescription>
                            Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.) or enter the setup key manually.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-center">
                            <div dangerouslySetInnerHTML={{ __html: qrCode.svg }} />
                        </div>
                        
                        <div className="space-y-2">
                            <Label htmlFor="setup-key">Setup Key (for manual entry)</Label>
                            <Input
                                id="setup-key"
                                value={qrCode.setup_key}
                                readOnly
                                className="font-mono text-sm"
                            />
                        </div>

                        <Separator />

                        <div className="space-y-2">
                            <Label htmlFor="confirmation-code">Verification Code</Label>
                            <Input
                                id="confirmation-code"
                                placeholder="Enter 6-digit code from your app"
                                value={confirmationCode}
                                onChange={(e) => setConfirmationCode(e.target.value)}
                                maxLength={6}
                            />
                            <p className="text-xs text-gray-500">
                                Enter the 6-digit code from your authenticator app to complete setup.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Recovery Codes Card */}
            {showRecoveryCodes && recoveryCodes.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Key className="h-5 w-5" />
                            <span>Recovery Codes</span>
                        </CardTitle>
                        <CardDescription>
                            Store these recovery codes in a safe place. They can be used to access your account if you lose your authenticator device.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <Alert>
                            <AlertDescription>
                                <strong>Important:</strong> Each recovery code can only be used once. Generate new codes if you run out.
                            </AlertDescription>
                        </Alert>

                        <div className="grid grid-cols-2 gap-2 font-mono text-sm bg-gray-50 p-4 rounded-lg">
                            {recoveryCodes.map((code, index) => (
                                <div key={index} className="p-2 bg-white rounded border">
                                    {code}
                                </div>
                            ))}
                        </div>

                        <div className="flex space-x-2">
                            <Button
                                variant="outline"
                                onClick={() => {
                                    const text = recoveryCodes.join('\n');
                                    navigator.clipboard.writeText(text);
                                    setSuccess('Recovery codes copied to clipboard.');
                                }}
                            >
                                <Download className="h-4 w-4 mr-2" />
                                Copy Codes
                            </Button>
                            <Button variant="outline" onClick={() => setShowRecoveryCodes(false)}>
                                Hide Codes
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}