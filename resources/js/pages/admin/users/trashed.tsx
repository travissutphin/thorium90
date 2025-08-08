import { CanAccess } from '@/components/CanAccess';
import { UserRoles } from '@/components/RoleBadge';
import { UserInfo } from '@/components/user-info';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, RotateCcw, Trash2 } from 'lucide-react';
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
    {
        title: 'Deleted Users',
        href: '/admin/users/trashed',
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
    deleted_at: string;
    [key: string]: unknown;
}

interface Stats {
    total_deleted: number;
    deleted_administrators: number;
    deleted_content_creators: number;
    deleted_subscribers: number;
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

export default function TrashedUsers({ users, stats }: Props) {
    const { hasPermission } = usePermissions();
    const [processingUser, setProcessingUser] = useState<number | null>(null);

    const handleRestoreUser = (userId: number) => {
        if (confirm('Are you sure you want to restore this user?')) {
            setProcessingUser(userId);
            router.patch(`/admin/users/${userId}/restore`, {}, {
                onFinish: () => setProcessingUser(null),
            });
        }
    };

    const handleForceDeleteUser = (userId: number) => {
        if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone!')) {
            setProcessingUser(userId);
            router.delete(`/admin/users/${userId}/force-delete`, {
                onFinish: () => setProcessingUser(null),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Deleted Users" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Deleted Users</h1>
                        <p className="text-muted-foreground">Manage soft-deleted users - restore or permanently delete</p>
                    </div>
                    
                    <Button variant="outline" asChild>
                        <Link href="/admin/users">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Users
                        </Link>
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Deleted</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_deleted}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Deleted Administrators</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.deleted_administrators}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Deleted Content Creators</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.deleted_content_creators}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Deleted Subscribers</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.deleted_subscribers}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Deleted Users List */}
                <Card>
                    <CardHeader>
                        <CardTitle>Deleted Users</CardTitle>
                        <CardDescription>Users that have been soft-deleted and can be restored</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {users.data.length === 0 ? (
                            <div className="text-center py-8">
                                <p className="text-muted-foreground">No deleted users found.</p>
                                <Button variant="outline" asChild className="mt-4">
                                    <Link href="/admin/users">View Active Users</Link>
                                </Button>
                            </div>
                        ) : (
                            <>
                                <div className="space-y-4">
                                    {users.data.map((user) => (
                                        <div key={user.id} className="flex items-center justify-between p-4 border rounded-lg bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-800">
                                            <div className="flex items-center gap-4">
                                                <UserInfo 
                                                    user={user} 
                                                    showEmail={true} 
                                                />
                                                <div className="flex flex-col gap-2">
                                                    <UserRoles roles={user.role_names} />
                                                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                        <span>Created: {new Date(user.created_at).toLocaleDateString()}</span>
                                                        <span className="text-red-600">• Deleted: {new Date(user.deleted_at).toLocaleDateString()}</span>
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
                                                <CanAccess permission="restore users">
                                                    <Button 
                                                        variant="outline" 
                                                        size="sm" 
                                                        className="text-green-600 hover:text-green-600 border-green-200 hover:border-green-300"
                                                        onClick={() => handleRestoreUser(user.id)}
                                                        disabled={processingUser === user.id}
                                                    >
                                                        <RotateCcw className="h-4 w-4" />
                                                    </Button>
                                                </CanAccess>
                                                
                                                <CanAccess permission="force delete users">
                                                    <Button 
                                                        variant="outline" 
                                                        size="sm" 
                                                        className="text-destructive hover:text-destructive"
                                                        onClick={() => handleForceDeleteUser(user.id)}
                                                        disabled={processingUser === user.id}
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
                            </>
                        )}
                    </CardContent>
                </Card>

                {/* Information Cards */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/20">
                        <CardContent className="pt-6">
                            <div className="flex items-start gap-3">
                                <div className="h-5 w-5 text-blue-600 mt-0.5">ℹ️</div>
                                <div>
                                    <h3 className="font-semibold text-blue-900 dark:text-blue-100">Soft Delete Information</h3>
                                    <ul className="text-sm text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                                        <li>• Deleted users are hidden from normal operations</li>
                                        <li>• User data and relationships are preserved</li>
                                        <li>• Users can be restored with all their data intact</li>
                                        <li>• Only Super Admins can permanently delete users</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/20">
                        <CardContent className="pt-6">
                            <div className="flex items-start gap-3">
                                <div className="h-5 w-5 text-amber-600 mt-0.5">⚠️</div>
                                <div>
                                    <h3 className="font-semibold text-amber-900 dark:text-amber-100">Permanent Deletion Warning</h3>
                                    <ul className="text-sm text-amber-700 dark:text-amber-300 mt-2 space-y-1">
                                        <li>• Permanent deletion cannot be undone</li>
                                        <li>• All user data will be completely removed</li>
                                        <li>• Related content may become orphaned</li>
                                        <li>• Consider data export before permanent deletion</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Permission Notice */}
                <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <div className="h-5 w-5 text-green-600 mt-0.5">🔒</div>
                            <div>
                                <h3 className="font-semibold text-green-900 dark:text-green-100">Permission-Based Actions</h3>
                                <p className="text-sm text-green-700 dark:text-green-300 mt-1">
                                    Available actions depend on your permissions:
                                </p>
                                <ul className="text-sm text-green-700 dark:text-green-300 mt-2 space-y-1">
                                    <li>• <strong>Restore Users:</strong> {hasPermission('restore users') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                    <li>• <strong>Force Delete Users:</strong> {hasPermission('force delete users') ? '✓ You have this permission' : '✗ You need this permission'}</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
