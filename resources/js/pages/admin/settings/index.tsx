import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import { 
    Settings, 
    ShieldCheck, 
    Users, 
    Lock, 
    Mail, 
    ToggleLeft, 
    Server,
    Save,
    RotateCcw,
    Download,
    Upload,
    BarChart3,
    AlertTriangle,
    CheckCircle,
    Info
} from 'lucide-react';
import AdminLayout from '@/layouts/admin-layout';

interface Setting {
    value: unknown;
    type: string;
    description: string;
    is_public: boolean;
}

interface SettingsGroup {
    [key: string]: Setting;
}

interface Settings {
    [category: string]: SettingsGroup;
}

interface Category {
    name: string;
    description: string;
    icon: string;
}

interface Categories {
    [key: string]: Category;
}

interface Stats {
    users: {
        total: number;
        active: number;
        deleted: number;
        by_role: { [role: string]: number };
    };
    settings: {
        total: number;
        by_category: { [category: string]: number };
        public: number;
    };
    system: {
        php_version: string;
        laravel_version: string;
        cache_enabled: boolean;
        debug_mode: boolean;
        maintenance_mode: boolean;
    };
}

interface Props {
    settings: Settings;
    categories: Categories;
    stats: Stats;
}

const iconMap = {
    'settings': Settings,
    'shield-check': ShieldCheck,
    'users': Users,
    'lock': Lock,
    'mail': Mail,
    'toggle-left': ToggleLeft,
    'server': Server,
};

export default function AdminSettingsIndex({ settings, categories, stats }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [activeTab, setActiveTab] = useState('application');
    const [formData, setFormData] = useState<Settings>(settings);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [hasChanges, setHasChanges] = useState(false);

    const handleSettingChange = (category: string, key: string, value: unknown) => {
        setFormData(prev => ({
            ...prev,
            [category]: {
                ...prev[category],
                [key]: {
                    ...prev[category][key],
                    value: value
                }
            }
        }));
        setHasChanges(true);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        // Flatten settings for submission
        const flattenedSettings: { [key: string]: any } = {};
        
        Object.entries(formData).forEach(([category, categorySettings]) => {
            Object.entries(categorySettings).forEach(([key, setting]) => {
                flattenedSettings[key] = {
                    value: setting.value,
                    type: setting.type,
                    category: category,
                    description: setting.description,
                    is_public: setting.is_public
                };
            });
        });

        router.put('/admin/settings', {
            settings: flattenedSettings
        }, {
            onSuccess: () => {
                setHasChanges(false);
            },
            onFinish: () => {
                setIsSubmitting(false);
            }
        });
    };

    const handleReset = (category?: string) => {
        if (confirm(category 
            ? `Are you sure you want to reset all ${categories[category]?.name} settings to defaults?`
            : 'Are you sure you want to reset ALL settings to defaults? This cannot be undone.'
        )) {
            router.post('/admin/settings/reset', {
                category: category
            });
        }
    };

    const handleExport = () => {
        window.open('/admin/settings/export', '_blank');
    };

    const renderSettingInput = (category: string, key: string, setting: Setting) => {
        const value = setting.value;
        const type = setting.type;

        switch (type) {
            case 'boolean':
                return (
                    <div className="flex items-center space-x-2">
                        <Switch
                            id={key}
                            checked={value}
                            onCheckedChange={(checked) => handleSettingChange(category, key, checked)}
                        />
                        <Label htmlFor={key} className="text-sm font-medium">
                            {value ? 'Enabled' : 'Disabled'}
                        </Label>
                    </div>
                );

            case 'integer':
                return (
                    <Input
                        type="number"
                        value={value}
                        onChange={(e) => handleSettingChange(category, key, parseInt(e.target.value) || 0)}
                        className="w-full"
                    />
                );

            case 'array':
                return (
                    <Textarea
                        value={Array.isArray(value) ? value.join(', ') : ''}
                        onChange={(e) => handleSettingChange(category, key, e.target.value.split(',').map(s => s.trim()).filter(Boolean))}
                        placeholder="Enter comma-separated values"
                        className="w-full"
                        rows={3}
                    />
                );

            case 'json':
                return (
                    <Textarea
                        value={typeof value === 'object' ? JSON.stringify(value, null, 2) : value}
                        onChange={(e) => {
                            try {
                                const parsed = JSON.parse(e.target.value);
                                handleSettingChange(category, key, parsed);
                            } catch {
                                // Invalid JSON, keep as string for now
                                handleSettingChange(category, key, e.target.value);
                            }
                        }}
                        placeholder="Enter valid JSON"
                        className="w-full font-mono text-sm"
                        rows={4}
                    />
                );

            default:
                // Special handling for specific settings
                if (key === 'auth.default_role') {
                    const roles = ['Super Admin', 'Admin', 'Editor', 'Author', 'Subscriber'];
                    return (
                        <Select value={value} onValueChange={(newValue) => handleSettingChange(category, key, newValue)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Select default role" />
                            </SelectTrigger>
                            <SelectContent>
                                {roles.map(role => (
                                    <SelectItem key={role} value={role}>{role}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    );
                }

                if (key.includes('password') || key.includes('secret')) {
                    return (
                        <Input
                            type="password"
                            value={value}
                            onChange={(e) => handleSettingChange(category, key, e.target.value)}
                            className="w-full"
                        />
                    );
                }

                if (setting.description?.includes('message') || key.includes('description')) {
                    return (
                        <Textarea
                            value={value}
                            onChange={(e) => handleSettingChange(category, key, e.target.value)}
                            className="w-full"
                            rows={3}
                        />
                    );
                }

                return (
                    <Input
                        type="text"
                        value={value}
                        onChange={(e) => handleSettingChange(category, key, e.target.value)}
                        className="w-full"
                    />
                );
        }
    };

    const renderCategorySettings = (category: string, categorySettings: SettingsGroup) => {
        const categoryInfo = categories[category];
        const IconComponent = iconMap[categoryInfo?.icon as keyof typeof iconMap] || Settings;

        return (
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                        <IconComponent className="h-6 w-6 text-primary" />
                        <div>
                            <h2 className="text-2xl font-bold">{categoryInfo?.name}</h2>
                            <p className="text-muted-foreground">{categoryInfo?.description}</p>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Badge variant="secondary">
                            {Object.keys(categorySettings).length} settings
                        </Badge>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleReset(category)}
                            disabled={isSubmitting}
                        >
                            <RotateCcw className="h-4 w-4 mr-2" />
                            Reset Category
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6">
                    {Object.entries(categorySettings).map(([key, setting]) => (
                        <Card key={key}>
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <div className="space-y-1">
                                        <CardTitle className="text-base font-medium">
                                            {key.split('.').pop()?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </CardTitle>
                                        <CardDescription className="text-sm">
                                            {setting.description}
                                        </CardDescription>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant={setting.is_public ? "default" : "secondary"} className="text-xs">
                                            {setting.is_public ? 'Public' : 'Private'}
                                        </Badge>
                                        <Badge variant="outline" className="text-xs">
                                            {setting.type}
                                        </Badge>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <Label htmlFor={key} className="text-sm font-medium">
                                        {key}
                                    </Label>
                                    {renderSettingInput(category, key, setting)}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        );
    };

    const renderSystemStats = () => (
        <div className="space-y-6">
            <div className="flex items-center space-x-3">
                <BarChart3 className="h-6 w-6 text-primary" />
                <div>
                    <h2 className="text-2xl font-bold">System Statistics</h2>
                    <p className="text-muted-foreground">Overview of system health and usage</p>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {/* User Statistics */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Users className="h-5 w-5" />
                            <span>Users</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between">
                            <span>Total Users:</span>
                            <Badge>{stats.users.total}</Badge>
                        </div>
                        <div className="flex justify-between">
                            <span>Active Users:</span>
                            <Badge variant="default">{stats.users.active}</Badge>
                        </div>
                        <div className="flex justify-between">
                            <span>Deleted Users:</span>
                            <Badge variant="destructive">{stats.users.deleted}</Badge>
                        </div>
                        <Separator />
                        <div className="space-y-2">
                            <h4 className="font-medium">By Role:</h4>
                            {Object.entries(stats.users.by_role).map(([role, count]) => (
                                <div key={role} className="flex justify-between text-sm">
                                    <span>{role}:</span>
                                    <span>{count}</span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Settings Statistics */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Settings className="h-5 w-5" />
                            <span>Settings</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between">
                            <span>Total Settings:</span>
                            <Badge>{stats.settings.total}</Badge>
                        </div>
                        <div className="flex justify-between">
                            <span>Public Settings:</span>
                            <Badge variant="default">{stats.settings.public}</Badge>
                        </div>
                        <Separator />
                        <div className="space-y-2">
                            <h4 className="font-medium">By Category:</h4>
                            {Object.entries(stats.settings.by_category).map(([category, count]) => (
                                <div key={category} className="flex justify-between text-sm">
                                    <span>{categories[category]?.name || category}:</span>
                                    <span>{count}</span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* System Information */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Server className="h-5 w-5" />
                            <span>System</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex justify-between">
                            <span>PHP Version:</span>
                            <Badge variant="outline">{stats.system.php_version}</Badge>
                        </div>
                        <div className="flex justify-between">
                            <span>Laravel Version:</span>
                            <Badge variant="outline">{stats.system.laravel_version}</Badge>
                        </div>
                        <Separator />
                        <div className="space-y-2">
                            <div className="flex justify-between items-center">
                                <span>Cache:</span>
                                <Badge variant={stats.system.cache_enabled ? "default" : "secondary"}>
                                    {stats.system.cache_enabled ? 'Enabled' : 'Disabled'}
                                </Badge>
                            </div>
                            <div className="flex justify-between items-center">
                                <span>Debug Mode:</span>
                                <Badge variant={stats.system.debug_mode ? "destructive" : "default"}>
                                    {stats.system.debug_mode ? 'On' : 'Off'}
                                </Badge>
                            </div>
                            <div className="flex justify-between items-center">
                                <span>Maintenance:</span>
                                <Badge variant={stats.system.maintenance_mode ? "destructive" : "default"}>
                                    {stats.system.maintenance_mode ? 'Active' : 'Inactive'}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );

    return (
        <AdminLayout>
            <Head title="System Settings" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">System Settings</h1>
                        <p className="text-muted-foreground">
                            Configure system-wide settings and monitor system health
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button variant="outline" onClick={handleExport}>
                            <Download className="h-4 w-4 mr-2" />
                            Export
                        </Button>
                        <Button variant="outline">
                            <Upload className="h-4 w-4 mr-2" />
                            Import
                        </Button>
                    </div>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <Alert>
                        <CheckCircle className="h-4 w-4" />
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                )}

                {flash?.error && (
                    <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                {/* Unsaved Changes Warning */}
                {hasChanges && (
                    <Alert>
                        <Info className="h-4 w-4" />
                        <AlertDescription>
                            You have unsaved changes. Don't forget to save your settings.
                        </AlertDescription>
                    </Alert>
                )}

                {/* Settings Form */}
                <form onSubmit={handleSubmit}>
                    <Tabs value={activeTab} onValueChange={setActiveTab}>
                        <div className="flex items-center justify-between">
                            <TabsList className="grid w-full grid-cols-8">
                                {Object.entries(categories).map(([key, category]) => {
                                    const IconComponent = iconMap[category.icon as keyof typeof iconMap] || Settings;
                                    return (
                                        <TabsTrigger key={key} value={key} className="flex items-center space-x-2">
                                            <IconComponent className="h-4 w-4" />
                                            <span className="hidden sm:inline">{category.name}</span>
                                        </TabsTrigger>
                                    );
                                })}
                                <TabsTrigger value="stats" className="flex items-center space-x-2">
                                    <BarChart3 className="h-4 w-4" />
                                    <span className="hidden sm:inline">Stats</span>
                                </TabsTrigger>
                            </TabsList>

                            {activeTab !== 'stats' && (
                                <div className="flex items-center space-x-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => handleReset()}
                                        disabled={isSubmitting}
                                    >
                                        <RotateCcw className="h-4 w-4 mr-2" />
                                        Reset All
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={isSubmitting || !hasChanges}
                                    >
                                        <Save className="h-4 w-4 mr-2" />
                                        {isSubmitting ? 'Saving...' : 'Save Changes'}
                                    </Button>
                                </div>
                            )}
                        </div>

                        {Object.entries(categories).map(([key, category]) => (
                            <TabsContent key={key} value={key} className="mt-6">
                                {formData[key] && renderCategorySettings(key, formData[key])}
                            </TabsContent>
                        ))}

                        <TabsContent value="stats" className="mt-6">
                            {renderSystemStats()}
                        </TabsContent>
                    </Tabs>
                </form>
            </div>
        </AdminLayout>
    );
}
