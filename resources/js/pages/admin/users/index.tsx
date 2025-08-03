import { CanAccess } from '@/components/CanAccess';
import { UserRoles } from '@/components/RoleBadge';
import { UserInfo } from '@/components/user-info';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Plus, Edit, Trash2, Shield } from 'lucide-react';

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

// Mock user data for demonstration
const mockUsers = [
    {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        role_names: ['Super Admin'],
        created_at: '2025-01-01T00:00:00Z',
    },
    {
        id: 2,
        name: 'Admin User',
        email: 'admin@example.com',
        role_names: ['Admin'],
        created_at: '2025-01-01T00:00:00Z',
    },
    {
        id: 3,
        name: 'Editor User',
        email: 'editor@example.com',
        role_names: ['Editor'],
        created_at: '2025-01-01T00:00:00Z',
    },
    {
        id: 4,
        name: 'Author User',
        email: 'author@example.com',
        role_names: ['Author'],
        created_at: '2025-01-01T00:00:00Z',
    },
    {
        id: 5,
        name: 'Subscriber User',
        email: 'subscriber@example.com',
        role_names: ['Subscriber'],
        created_at: '2025-01-01T00:00:00Z',
    },
];

export default function UsersIndex() {
    const { hasPermission } = usePermissions();

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
                    
                    <CanAccess permission="create users">
                        <Button asChild>
                            <Link href="/admin/users/create">
                                <Plus className="h-4 w-4 mr-2" />
                                Add User
                            </Link>
                        </Button>
                    </CanAccess>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{mockUsers.length}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Administrators</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {mockUsers.filter(user => user.role_names.some(role => ['Super Admin', 'Admin'].includes(role))).length}
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Content Creators</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {mockUsers.filter(user => user.role_names.some(role => ['Editor', 'Author'].includes(role))).length}
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Subscribers</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {mockUsers.filter(user => user.role_names.includes('Subscriber')).length}
                            </div>
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
                            {mockUsers.map((user) => (
                                <div key={user.id} className="flex items-center justify-between p-4 border rounded-lg">
                                    <div className="flex items-center gap-4">
                                        <UserInfo 
                                            user={user as any} 
                                            showEmail={true} 
                                        />
                                        <div className="flex flex-col gap-2">
                                            <UserRoles roles={user.role_names} />
                                            <span className="text-xs text-muted-foreground">
                                                Created: {new Date(user.created_at).toLocaleDateString()}
                                            </span>
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
                                            <Button variant="outline" size="sm" className="text-destructive hover:text-destructive">
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </CanAccess>
                                    </div>
                                </div>
                            ))}
                        </div>
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
