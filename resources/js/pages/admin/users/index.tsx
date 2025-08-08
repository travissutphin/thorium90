 import { CanAccess } from '@/components/CanAccess';
import { UserRoles } from '@/components/RoleBadge';
import { UserInfo } from '@/components/user-info';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit, Trash2, Shield } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Users',
        href: '/admin/users',
    },
];

interface UserRole {
    id: number;
    name: string;
    guard_name: string;
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
    }>;
    created_at: string;
    updated_at: string;
}

interface UserData {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    provider?: string | null;
    avatar?: string;
    roles: UserRole[];
    permissions: Array<{
        id: number;
        name: string;
        guard_name: string;
        created_at: string;
        updated_at: string;
    }>;
    role_names: string[];
    all_permissions: string[];
    is_social_user: boolean;
    avatar_url: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

interface Stats {
    total_users: number;
    administrators: number;
    content_creators: number;
    subscribers: number;
    verified_users: number;
    social_users: number;
}

interface PaginationLink {
    url?: string;
    label: string;
    active: boolean;
}

interface Props {
    users: {
        data: UserData[];
        links: PaginationLink[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    stats: Stats;
}

export default function UsersIndex({ users, stats }: Props) {
    const { hasPermission } = usePermissions();
    const [deletingUser, setDeletingUser] = useState<number | null>(null);

    const handleDeleteUser = (userId: number) => {
        if (confirm('Are you sure you want to delete this user? The user will be moved to the deleted users list and can be restored later.')) {
            setDeletingUser(userId);
            router.delete(`/admin/users/${userId}`, {
                onFinish: () => setDeletingUser(null),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="User Management" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">User Management</h1>
                        <p className="text-muted-foreground">Manage users and their roles</p>
                    </div>
                    
                    <div className="flex gap-2">
                        <CanAccess permission="view users">
                            <Button variant="outline" asChild>
                                <Link href="/admin/users/trashed">
                                    <Trash2 className="h-4 w-4 mr-2" />
                                    Deleted Users
                                </Link>
                            </Button>
                        </CanAccess>
                        
                        <CanAccess permission="create users">
                            <Button asChild>
                                <Link href="/admin/users/create">
                                    <Plus className="h-4 w-4 mr-2" />
                                    Add User
                                </Link>
                            </Button>
                        </CanAccess>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_users}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Administrators</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.administrators}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Content Creators</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.content_creators}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Subscribers</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.subscribers}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Users List */}
                <Card>
                    <CardHeader>
                        <CardTitle>Users</CardTitle>
                        <CardDescription>A list of all users in the system</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {users.data.map((user) => (
                                <div key={user.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center gap-4">
                                        <UserInfo 
                                            user={user} 
                                            showEmail={true} 
                                        />
                                        <div className="flex flex-col gap-2">
                                            <UserRoles roles={user.role_names} />
                                            <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                <span>Created: {new Date(user.created_at).toLocaleDateString()}</span>
                                                {user.email_verified_at && (
                                                    <span className="text-green-600">• Verified</span>
                                                )}
                                                {user.is_social_user && (
                                                    <span className="text-blue-600">• Social Login</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center gap-2">
                                        <CanAccess permission="edit users">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={`/admin/users/${user.id}/edit`}>
                                                    <Edit className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        </CanAccess>
                                        
                                        <CanAccess permission="manage user roles">
                                            <Button variant="outline" size="sm" asChild>
                                                <Link href={`/admin/users/${user.id}/roles`}>
                                                    <Shield className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                        </CanAccess>
                                        
                                        <CanAccess permission="delete users">
                                            <Button 
                                                variant="outline" 
                                                size="sm" 
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => handleDeleteUser(user.id)}
                                                disabled={deletingUser === user.id}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </CanAccess>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Pagination */}
                        {users.links && users.links.length > 3 && (
                            <div className="flex justify-center mt-6">
                                <div className="flex gap-2">
                                    {users.links.map((link, index) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? "default" : "outline"}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url)}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Permission Notice */}
                <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/20">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <Shield className="h-5 w-5 text-blue-600 mt-0.5" />
                            <div>
                                <h3 className="font-semibold text-blue-900 dark:text-blue-100">Permission-Based Access</h3>
                                <p className="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                    This page demonstrates role-based access control. Different users will see different actions based on their permissions:
                                </p>
                                <ul className="text-sm text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                                    <li>• <strong>View Users:</strong> {hasPermission('view users') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                    <li>• <strong>Create Users:</strong> {hasPermission('create users') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                    <li>• <strong>Edit Users:</strong> {hasPermission('edit users') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                    <li>• <strong>Delete Users:</strong> {hasPermission('delete users') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                    <li>• <strong>Manage Roles:</strong> {hasPermission('manage user roles') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
