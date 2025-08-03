import { RoleBadge, UserRoles as UserRolesComponent } from '@/components/RoleBadge';
import { UserInfo } from '@/components/user-info';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Save, ArrowLeft, Shield, Users } from 'lucide-react';

interface Role {
    id: number;
    name: string;
    permissions_count: number;
    users_count: number;
}

interface UserRole {
    id: number;
    name: string;
    permissions: string[];
}

interface User {
    id: number;
    name: string;
    email: string;
    roles: UserRole[];
    role_names: string[];
    all_permissions: string[];
    created_at: string;
}

interface Props {
    user: User;
    availableRoles: Role[];
}

export default function UserRoles({ user, availableRoles }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin',
            href: '/admin',
        },
        {
            title: 'Users',
            href: '/admin/users',
        },
        {
            title: user.name,
            href: `/admin/users/${user.id}/roles`,
        },
    ];

    const { data, setData, put, processing, errors } = useForm({
        roles: user.role_names,
    });

    const handleRoleChange = (roleName: string, checked: boolean) => {
        if (checked) {
            setData('roles', [...data.roles, roleName]);
        } else {
            setData('roles', data.roles.filter(role => role !== roleName));
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.users.roles.update', user.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Manage Roles - ${user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" size="sm" asChild>
                            <a href={route('admin.users.index')}>
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Users
                            </a>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold tracking-tight">Manage User Roles</h1>
                            <p className="text-muted-foreground">Assign or remove roles for {user.name}</p>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* User Information */}
                    <div className="lg:col-span-1">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Users className="h-5 w-5" />
                                    User Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center gap-3">
                                    <UserInfo user={user as any} showEmail={true} />
                                </div>
                                
                                <div>
                                    <h4 className="font-medium mb-2">Current Roles</h4>
                                    <UserRolesComponent roles={user.role_names} />
                                </div>

                                <div>
                                    <h4 className="font-medium mb-2">Member Since</h4>
                                    <p className="text-sm text-muted-foreground">
                                        {new Date(user.created_at).toLocaleDateString()}
                                    </p>
                                </div>

                                <div>
                                    <h4 className="font-medium mb-2">Total Permissions</h4>
                                    <p className="text-sm text-muted-foreground">
                                        {user.all_permissions.length} permissions granted
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Current Permissions */}
                        <Card className="mt-6">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Shield className="h-5 w-5" />
                                    Current Permissions
                                </CardTitle>
                                <CardDescription>
                                    All permissions granted through assigned roles
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2 max-h-64 overflow-y-auto">
                                    {user.all_permissions.map((permission) => (
                                        <div key={permission} className="flex items-center gap-2 text-sm">
                                            <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                            {permission}
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Role Assignment */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Assign Roles</CardTitle>
                                <CardDescription>
                                    Select the roles you want to assign to this user
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        {availableRoles.map((role) => (
                                            <div key={role.id} className="flex items-start space-x-3 p-4 border rounded-lg">
                                                <Checkbox
                                                    id={`role-${role.id}`}
                                                    checked={data.roles.includes(role.name)}
                                                    onCheckedChange={(checked) => 
                                                        handleRoleChange(role.name, checked as boolean)
                                                    }
                                                />
                                                <div className="flex-1 space-y-2">
                                                    <div className="flex items-center gap-2">
                                                        <RoleBadge role={role.name} />
                                                    </div>
                                                    <div className="text-sm text-muted-foreground">
                                                        {role.permissions_count} permissions • {role.users_count} users
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {errors.roles && (
                                        <div className="text-sm text-red-600">
                                            {errors.roles}
                                        </div>
                                    )}

                                    <div className="flex items-center gap-4">
                                        <Button type="submit" disabled={processing}>
                                            <Save className="h-4 w-4 mr-2" />
                                            {processing ? 'Saving...' : 'Save Changes'}
                                        </Button>
                                        
                                        <Button type="button" variant="outline" asChild>
                                            <a href={route('admin.users.index')}>
                                                Cancel
                                            </a>
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Role Comparison */}
                        <Card className="mt-6">
                            <CardHeader>
                                <CardTitle>Role Comparison</CardTitle>
                                <CardDescription>
                                    Compare permissions across different roles
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {availableRoles.map((role) => (
                                        <div key={role.id} className="flex items-center justify-between p-3 border rounded">
                                            <div className="flex items-center gap-3">
                                                <RoleBadge role={role.name} />
                                                <span className="text-sm text-muted-foreground">
                                                    {role.permissions_count} permissions
                                                </span>
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {role.users_count} users
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Security Notice */}
                <Card className="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/20">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <Shield className="h-5 w-5 text-amber-600 mt-0.5" />
                            <div>
                                <h3 className="font-semibold text-amber-900 dark:text-amber-100">Security Considerations</h3>
                                <p className="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                    Role assignments affect user permissions and system access. Please consider the following:
                                </p>
                                <ul className="text-sm text-amber-700 dark:text-amber-300 mt-2 space-y-1">
                                    <li>• Super Admin role grants full system access</li>
                                    <li>• Role changes take effect immediately</li>
                                    <li>• Users inherit all permissions from assigned roles</li>
                                    <li>• At least one Super Admin must exist at all times</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
