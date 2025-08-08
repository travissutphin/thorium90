import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Shield } from 'lucide-react';
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
        title: 'Create Role',
        href: '/admin/roles/create',
    },
];

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Props {
    permissions: Record<string, Permission[]>;
}

export default function CreateRole({ permissions }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        permissions: [] as string[],
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.roles.store'), {
            onSuccess: () => reset(),
        });
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Role" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Create New Role</h1>
                        <p className="text-muted-foreground">Define a new role and assign permissions</p>
                    </div>
                    
                    <Button variant="outline" asChild>
                        <Link href={route('admin.roles.index')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Roles
                        </Link>
                    </Button>
                </div>

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
                            <div className="space-y-2">
                                <Label htmlFor="name">Role Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Enter role name (e.g., Content Manager)"
                                    className={errors.name ? 'border-destructive' : ''}
                                />
                                {errors.name && (
                                    <p className="text-sm text-destructive">{errors.name}</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Permissions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Permissions</CardTitle>
                            <CardDescription>
                                Select the permissions this role should have. You can select individual permissions or entire groups.
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
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {data.permissions.length > 0 && (
                                <div className="mt-6 p-4 bg-muted rounded-lg">
                                    <h4 className="font-medium mb-2">Selected Permissions ({data.permissions.length})</h4>
                                    <div className="flex flex-wrap gap-2">
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
                            )}
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('admin.roles.index')}>
                                Cancel
                            </Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creating...' : 'Create Role'}
                        </Button>
                    </div>
                </form>

                {/* Security Notice */}
                <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/20">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <Shield className="h-5 w-5 text-blue-600 mt-0.5" />
                            <div>
                                <h3 className="font-semibold text-blue-900 dark:text-blue-100">Role Creation Guidelines</h3>
                                <ul className="text-sm text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                                    <li>• Choose a descriptive name that clearly indicates the role's purpose</li>
                                    <li>• Only assign permissions that are necessary for the role's responsibilities</li>
                                    <li>• Consider the principle of least privilege when selecting permissions</li>
                                    <li>• Role names must be unique across the system</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
