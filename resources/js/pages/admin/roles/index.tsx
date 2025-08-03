import { RoleBadge } from '@/components/RoleBadge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit, Trash2, Users, Shield, Key } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Roles & Permissions',
        href: '/admin/roles',
    },
];

interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: string[];
    permissions_count: number;
    users_count: number;
    created_at: string;
    updated_at: string;
}

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Props {
    roles: Role[];
    permissions: Record<string, Permission[]>;
}

export default function RolesIndex({ roles, permissions }: Props) {
    const handleDeleteRole = (roleId: number, roleName: string) => {
        if (confirm(`Are you sure you want to delete the "${roleName}" role?`)) {
            router.delete(route('admin.roles.destroy', roleId));
        }
    };

    const totalPermissions = Object.values(permissions).reduce((total, group) => total + group.length, 0);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Roles & Permissions" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Roles & Permissions</h1>
                        <p className="text-muted-foreground">Manage system roles and their permissions</p>
                    </div>
                    
                    <Button asChild>
                        <Link href={route('admin.roles.create')}>
                            <Plus className="h-4 w-4 mr-2" />
                            Create Role
                        </Link>
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Roles</CardTitle>
                            <Shield className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{roles.length}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Permissions</CardTitle>
                            <Key className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{totalPermissions}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Permission Groups</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{Object.keys(permissions).length}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Roles List */}
                <Card>
                    <CardHeader>
                        <CardTitle>System Roles</CardTitle>
                        <CardDescription>Manage roles and their associated permissions</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {roles.map((role) => (
                                <div key={role.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center gap-4">
                                        <RoleBadge role={role.name} />
                                        <div className="flex flex-col gap-1">
                                            <div className="flex items-center gap-2">
                                                <span className="font-medium">{role.name}</span>
                                                <span className="text-xs text-muted-foreground">
                                                    {role.permissions_count} permissions
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                <Users className="h-3 w-3" />
                                                {role.users_count} users assigned
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center gap-2">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link href={route('admin.roles.edit', role.id)}>
                                                <Edit className="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        
                                        {role.name !== 'Super Admin' && (
                                            <Button 
                                                variant="outline" 
                                                size="sm" 
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => handleDeleteRole(role.id, role.name)}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Permission Groups Overview */}
                <Card>
                    <CardHeader>
                        <CardTitle>Permission Groups</CardTitle>
                        <CardDescription>Overview of available permissions by category</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {Object.entries(permissions).map(([groupName, groupPermissions]) => (
                                <div key={groupName} className="p-4 border rounded-lg">
                                    <h3 className="font-semibold capitalize mb-2">{groupName}</h3>
                                    <p className="text-sm text-muted-foreground mb-3">
                                        {groupPermissions.length} permissions
                                    </p>
                                    <div className="space-y-1">
                                        {groupPermissions.slice(0, 3).map((permission) => (
                                            <div key={permission.id} className="text-xs text-muted-foreground">
                                                • {permission.name}
                                            </div>
                                        ))}
                                        {groupPermissions.length > 3 && (
                                            <div className="text-xs text-muted-foreground">
                                                ... and {groupPermissions.length - 3} more
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Super Admin Notice */}
                <Card className="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/20">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <Shield className="h-5 w-5 text-amber-600 mt-0.5" />
                            <div>
                                <h3 className="font-semibold text-amber-900 dark:text-amber-100">Super Admin Access Required</h3>
                                <p className="text-sm text-amber-700 dark:text-amber-300 mt-1">
                                    Role and permission management is restricted to Super Admin users only. This ensures system security and prevents unauthorized privilege escalation.
                                </p>
                                <ul className="text-sm text-amber-700 dark:text-amber-300 mt-2 space-y-1">
                                    <li>• Super Admin role cannot be deleted</li>
                                    <li>• At least one Super Admin must exist at all times</li>
                                    <li>• Role changes are logged for security auditing</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
