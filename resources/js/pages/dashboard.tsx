import { CanAccess, CanAccessAdmin, CanAccessContentCreator, CanAccessSuperAdmin } from '@/components/CanAccess';
import { UserRoles } from '@/components/RoleBadge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Users, FileText, Settings, Shield, Eye, Plus } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;
    const user = auth?.user;
    const userRoles = user?.role_names as string[] || [];
    const userPermissions = user?.permission_names as string[] || [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Welcome Section */}
                <div className="space-y-2">
                    <h1 className="text-2xl font-bold tracking-tight">Welcome back, {user?.name}!</h1>
                    <div className="flex items-center gap-2">
                        <span className="text-muted-foreground">Your roles:</span>
                        <UserRoles roles={userRoles} />
                    </div>
                </div>

                {/* Quick Stats */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <CanAccess permission="view dashboard">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Dashboard Access</CardTitle>
                                <Eye className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">✓</div>
                                <p className="text-xs text-muted-foreground">You can view the dashboard</p>
                            </CardContent>
                        </Card>
                    </CanAccess>

                    <CanAccessContentCreator>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Content Creation</CardTitle>
                                <FileText className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">✓</div>
                                <p className="text-xs text-muted-foreground">You can create content</p>
                            </CardContent>
                        </Card>
                    </CanAccessContentCreator>

                    <CanAccessAdmin>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">User Management</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">✓</div>
                                <p className="text-xs text-muted-foreground">You can manage users</p>
                            </CardContent>
                        </Card>
                    </CanAccessAdmin>

                    <CanAccessSuperAdmin>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">System Admin</CardTitle>
                                <Shield className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">✓</div>
                                <p className="text-xs text-muted-foreground">Full system access</p>
                            </CardContent>
                        </Card>
                    </CanAccessSuperAdmin>
                </div>

                {/* Quick Actions */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>Common tasks based on your permissions</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            <CanAccess permission="create posts">
                                <Link 
                                    href="/content/posts/create" 
                                    className="flex items-center gap-2 p-2 rounded-md hover:bg-muted transition-colors"
                                >
                                    <Plus className="h-4 w-4" />
                                    Create New Post
                                </Link>
                            </CanAccess>
                            
                            <CanAccess permission="view users">
                                <Link 
                                    href="/admin/users" 
                                    className="flex items-center gap-2 p-2 rounded-md hover:bg-muted transition-colors"
                                >
                                    <Users className="h-4 w-4" />
                                    Manage Users
                                </Link>
                            </CanAccess>

                            <CanAccess permission="manage settings">
                                <Link 
                                    href="/admin/settings" 
                                    className="flex items-center gap-2 p-2 rounded-md hover:bg-muted transition-colors"
                                >
                                    <Settings className="h-4 w-4" />
                                    System Settings
                                </Link>
                            </CanAccess>

                            <CanAccess role="Super Admin">
                                <Link 
                                    href="/admin/roles" 
                                    className="flex items-center gap-2 p-2 rounded-md hover:bg-muted transition-colors"
                                >
                                    <Shield className="h-4 w-4" />
                                    Manage Roles & Permissions
                                </Link>
                            </CanAccess>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Your Permissions</CardTitle>
                            <CardDescription>Permissions granted to your roles</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2 max-h-48 overflow-y-auto">
                                {userPermissions.map((permission) => (
                                    <div key={permission} className="flex items-center gap-2 text-sm">
                                        <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                        {permission}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Area */}
                <div className="relative min-h-[40vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Role-Based Content Area</h3>
                        
                        <CanAccessSuperAdmin fallback={
                            <CanAccessAdmin fallback={
                                <CanAccessContentCreator fallback={
                                    <div className="text-center py-8 text-muted-foreground">
                                        <p>Welcome! You have basic dashboard access.</p>
                                        <p className="text-sm mt-2">Contact an administrator to request additional permissions.</p>
                                    </div>
                                }>
                                    <div className="text-center py-8">
                                        <FileText className="h-12 w-12 mx-auto mb-4 text-blue-500" />
                                        <h4 className="text-lg font-semibold mb-2">Content Creator Dashboard</h4>
                                        <p className="text-muted-foreground">You can create and manage content.</p>
                                    </div>
                                </CanAccessContentCreator>
                            }>
                                <div className="text-center py-8">
                                    <Users className="h-12 w-12 mx-auto mb-4 text-orange-500" />
                                    <h4 className="text-lg font-semibold mb-2">Administrator Dashboard</h4>
                                    <p className="text-muted-foreground">You have administrative access to manage users and settings.</p>
                                </div>
                            </CanAccessAdmin>
                        }>
                            <div className="text-center py-8">
                                <Shield className="h-12 w-12 mx-auto mb-4 text-red-500" />
                                <h4 className="text-lg font-semibold mb-2">Super Administrator Dashboard</h4>
                                <p className="text-muted-foreground">You have full system access including role and permission management.</p>
                            </div>
                        </CanAccessSuperAdmin>
                    </div>
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10 -z-10" />
                </div>
            </div>
        </AppLayout>
    );
}
