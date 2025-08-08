import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { RoleBadge } from '@/components/RoleBadge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Shield, Users, Calendar } from 'lucide-react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Roles & Permissions',
        href: '/admin/roles',
    },
    {
        title: 'Edit Role',
        href: '#',
    },
];

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: string[];
    users_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    role: Role;
    permissions: Record<string, Permission[]>;
}

export default function EditRole({ role, permissions }: Props) {
    const { data, setData, put, processing, errors, isDirty } = useForm({
        name: role.name,
        permissions: role.permissions,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('admin.roles.update', role.id));
    };

    const handlePermissionChange = (permissionName: string, checked: boolean) => {
        if (checked) {
            setData('permissions', [...data.permissions, permissionName]);
        } else {
            setData('permissions', data.permissions.filter(p => p !== permissionName));
        }
    };

    const handleGroupToggle = (groupPermissions: Permission[], checked: boolean) => {
        const groupPermissionNames = groupPermissions.map(p => p.name);
        
        if (checked) {
            // Add all permissions from this group that aren't already selected
            const newPermissions = [...data.permissions];
            groupPermissionNames.forEach(name => {
                if (!newPermissions.includes(name)) {
                    newPermissions.push(name);
                }
            });
            setData('permissions', newPermissions);
        } else {
            // Remove all permissions from this group
            setData('permissions', data.permissions.filter(p => !groupPermissionNames.includes(p)));
        }
    };

    const isGroupFullySelected = (groupPermissions: Permission[]) => {
        return groupPermissions.every(p => data.permissions.includes(p.name));
    };

    const isGroupPartiallySelected = (groupPermissions: Permission[]) => {
        return groupPermissions.some(p => data.permissions.includes(p.name)) && 
               !isGroupFullySelected(groupPermissions);
    };

    const isSuperAdmin = role.name === 'Super Admin';
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Role: ${role.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Edit Role</h1>
                        <p className="text-muted-foreground">Modify role details and permissions</p>
                    </div>
                    
                    <Button variant="outline" asChild>
                        <Link href={route('admin.roles.index')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Roles
                        </Link>
                    </Button>
                </div>

                {/* Super Admin Warning */}
                {isSuperAdmin && (
                    <Card className="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/20">
                        <CardContent className="pt-6">
                            <div className="flex items-start gap-3">
                                <Shield className="h-5 w-5 text-amber-600 mt-0.5" />
                                <div>
                                    <h3 className="font-semibold text-amber-900 dark:text-amber-100">Super Admin Role</h3>
                                    <p className="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                        You are editing the Super Admin role. The role name cannot be changed for security reasons, 
                                        but you can modify permissions. Exercise caution when removing permissions as this affects 
                                        all Super Admin users.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Role Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                Role Details
                            </CardTitle>
                            <CardDescription>
                                Basic information about the role
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Role Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Enter role name"
                                        disabled={isSuperAdmin}
                                        className={errors.name ? 'border-destructive' : ''}
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">{errors.name}</p>
                                    )}
                                    {isSuperAdmin && (
                                        <p className="text-xs text-muted-foreground">
                                            Super Admin role name cannot be changed
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label>Current Role Badge</Label>
                                    <div className="flex items-center gap-2">
                                        <RoleBadge role={role.name} />
                                        <span className="text-sm text-muted-foreground">
                                            Preview of role badge
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Role Statistics */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t">
                                <div className="flex items-center gap-2">
                                    <Users className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p className="text-sm font-medium">{role.users_count}</p>
                                        <p className="text-xs text-muted-foreground">Users assigned</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p className="text-sm font-medium">Created</p>
                                        <p className="text-xs text-muted-foreground">{formatDate(role.created_at)}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p className="text-sm font-medium">Last Updated</p>
                                        <p className="text-xs text-muted-foreground">{formatDate(role.updated_at)}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Permissions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Permissions</CardTitle>
                            <CardDescription>
                                Modify the permissions this role should have. You can select individual permissions or entire groups.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {errors.permissions && (
                                <div className="mb-4 p-3 bg-destructive/10 border border-destructive/20 rounded-md">
                                    <p className="text-sm text-destructive">{errors.permissions}</p>
                                </div>
                            )}
                            
                            <div className="space-y-6">
                                {Object.entries(permissions).map(([groupName, groupPermissions]) => (
                                    <div key={groupName} className="space-y-3">
                                        <div className="flex items-center space-x-2 pb-2 border-b">
                                            <Checkbox
                                                id={`group-${groupName}`}
                                                checked={isGroupFullySelected(groupPermissions)}
                                                onCheckedChange={(checked) => 
                                                    handleGroupToggle(groupPermissions, checked as boolean)
                                                }
                                                className={isGroupPartiallySelected(groupPermissions) ? 'data-[state=checked]:bg-orange-500' : ''}
                                            />
                                            <Label 
                                                htmlFor={`group-${groupName}`}
                                                className="text-base font-semibold capitalize cursor-pointer"
                                            >
                                                {groupName} ({groupPermissions.length} permissions)
                                            </Label>
                                            {isGroupPartiallySelected(groupPermissions) && (
                                                <span className="text-xs text-orange-600 bg-orange-100 px-2 py-1 rounded">
                                                    Partially selected
                                                </span>
                                            )}
                                        </div>
                                        
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 ml-6">
                                            {groupPermissions.map((permission) => (
                                                <div key={permission.id} className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id={`permission-${permission.id}`}
                                                        checked={data.permissions.includes(permission.name)}
                                                        onCheckedChange={(checked) => 
                                                            handlePermissionChange(permission.name, checked as boolean)
                                                        }
                                                    />
                                                    <Label 
                                                        htmlFor={`permission-${permission.id}`}
                                                        className="text-sm cursor-pointer"
                                                    >
                                                        {permission.name}
                                                    </Label>
                                                    {role.permissions.includes(permission.name) && 
                                                     !data.permissions.includes(permission.name) && (
                                                        <span className="text-xs text-red-600">
                                                            (removing)
                                                        </span>
                                                    )}
                                                    {!role.permissions.includes(permission.name) && 
                                                     data.permissions.includes(permission.name) && (
                                                        <span className="text-xs text-green-600">
                                                            (adding)
                                                        </span>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Permission Summary */}
                            <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="p-4 bg-muted rounded-lg">
                                    <h4 className="font-medium mb-2">Current Permissions ({data.permissions.length})</h4>
                                    <div className="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                                        {data.permissions.map((permission) => (
                                            <span 
                                                key={permission}
                                                className="inline-flex items-center px-2 py-1 rounded-md bg-primary/10 text-primary text-xs"
                                            >
                                                {permission}
                                            </span>
                                        ))}
                                    </div>
                                </div>

                                {isDirty && (
                                    <div className="p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                        <h4 className="font-medium mb-2 text-blue-900 dark:text-blue-100">Changes Summary</h4>
                                        <div className="space-y-2 text-sm">
                                            {role.permissions.filter(p => !data.permissions.includes(p)).length > 0 && (
                                                <div>
                                                    <span className="text-red-600 font-medium">Removing:</span>
                                                    <span className="ml-2">
                                                        {role.permissions.filter(p => !data.permissions.includes(p)).length} permissions
                                                    </span>
                                                </div>
                                            )}
                                            {data.permissions.filter(p => !role.permissions.includes(p)).length > 0 && (
                                                <div>
                                                    <span className="text-green-600 font-medium">Adding:</span>
                                                    <span className="ml-2">
                                                        {data.permissions.filter(p => !role.permissions.includes(p)).length} permissions
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('admin.roles.index')}>
                                Cancel
                            </Link>
                        </Button>
                        <Button type="submit" disabled={processing || !isDirty}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Updating...' : 'Update Role'}
                        </Button>
                    </div>
                </form>

                {/* Impact Warning */}
                {role.users_count > 0 && isDirty && (
                    <Card className="border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950/20">
                        <CardContent className="pt-6">
                            <div className="flex items-start gap-3">
                                <Users className="h-5 w-5 text-orange-600 mt-0.5" />
                                <div>
                                    <h3 className="font-semibold text-orange-900 dark:text-orange-100">Impact Warning</h3>
                                    <p className="text-sm text-orange-700 dark:text-orange-300 mt-1">
                                        This role is currently assigned to <strong>{role.users_count}</strong> user{role.users_count !== 1 ? 's' : ''}. 
                                        Changes to permissions will immediately affect all users with this role.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
