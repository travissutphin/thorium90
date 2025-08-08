import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEventHandler } from 'react';

interface Role {
    id: number;
    name: string;
    permissions_count: number;
    users_count: number;
}

interface UserData {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    provider?: string | null;
    avatar?: string;
    role_names: string[];
    is_social_user: boolean;
    avatar_url: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    user: UserData;
    roles: Role[];
}

export default function EditUser({ user, roles }: Props) {
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
            title: `Edit ${user.name}`,
            href: `/admin/users/${user.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors } = useForm<{
        name: string;
        email: string;
        password: string;
        password_confirmation: string;
        roles: string[];
        email_verified: boolean;
    }>({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        roles: user.role_names,
        email_verified: !!user.email_verified_at,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('admin.users.update', user.id));
    };

    const handleRoleChange = (roleName: string, checked: boolean) => {
        if (checked) {
            setData('roles', [...data.roles, roleName]);
        } else {
            setData('roles', data.roles.filter(role => role !== roleName));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Edit User</h1>
                        <p className="text-muted-foreground">Update user information and permissions</p>
                    </div>
                    
                    <Button variant="outline" asChild>
                        <Link href="/admin/users">
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Users
                        </Link>
                    </Button>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-6 md:grid-cols-2">
                        {/* User Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>User Information</CardTitle>
                                <CardDescription>Basic user details and credentials</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoFocus
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-destructive">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-destructive">{errors.email}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">New Password (optional)</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Leave blank to keep current password"
                                    />
                                    {errors.password && (
                                        <p className="text-sm text-destructive">{errors.password}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password_confirmation">Confirm New Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        placeholder="Confirm new password"
                                    />
                                    {errors.password_confirmation && (
                                        <p className="text-sm text-destructive">{errors.password_confirmation}</p>
                                    )}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="email_verified"
                                        checked={data.email_verified}
                                        onCheckedChange={(checked) => setData('email_verified', !!checked)}
                                    />
                                    <Label htmlFor="email_verified">Email verified</Label>
                                </div>

                                {/* User Status Information */}
                                <div className="pt-4 border-t space-y-2">
                                    <h4 className="font-medium">Account Information</h4>
                                    <div className="text-sm text-muted-foreground space-y-1">
                                        <p>Created: {new Date(user.created_at).toLocaleDateString()}</p>
                                        <p>Last updated: {new Date(user.updated_at).toLocaleDateString()}</p>
                                        {user.is_social_user && (
                                            <p className="text-blue-600">Social login account ({user.provider})</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Role Assignment */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Role Assignment</CardTitle>
                                <CardDescription>Assign roles to define user permissions</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {roles.map((role) => (
                                        <div key={role.id} className="flex items-start space-x-3 p-3 border rounded-lg">
                                            <Checkbox
                                                id={`role-${role.id}`}
                                                checked={data.roles.includes(role.name)}
                                                onCheckedChange={(checked) => handleRoleChange(role.name, !!checked)}
                                            />
                                            <div className="flex-1">
                                                <Label htmlFor={`role-${role.id}`} className="font-medium">
                                                    {role.name}
                                                </Label>
                                                <p className="text-sm text-muted-foreground">
                                                    {role.permissions_count} permissions • {role.users_count} users
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                    {errors.roles && (
                                        <p className="text-sm text-destructive">{errors.roles}</p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-4">
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/users">Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Updating...' : 'Update User'}
                        </Button>
                    </div>
                </form>

                {/* Information Cards */}
                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/20">
                        <CardContent className="pt-6">
                            <div className="flex items-start gap-3">
                                <div className="h-5 w-5 text-blue-600 mt-0.5">ℹ️</div>
                                <div>
                                    <h3 className="font-semibold text-blue-900 dark:text-blue-100">Update Guidelines</h3>
                                    <ul className="text-sm text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                                        <li>• Leave password fields blank to keep current password</li>
                                        <li>• Email changes require re-verification</li>
                                        <li>• Role changes take effect immediately</li>
                                        <li>• Social login users may have limited edit options</li>
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
                                    <h3 className="font-semibold text-amber-900 dark:text-amber-100">Security Notice</h3>
                                    <ul className="text-sm text-amber-700 dark:text-amber-300 mt-2 space-y-1">
                                        <li>• Cannot remove Super Admin role from last Super Admin</li>
                                        <li>• Users cannot edit their own account through this interface</li>
                                        <li>• Password changes will log out the user from all devices</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
