import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Users, Settings, FileText, Image, Shield } from 'lucide-react';
import AppLogo from './app-logo';

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const user = auth?.user;

    // Build navigation items based on user permissions
    const getNavItems = (): NavItem[] => {
        const items: NavItem[] = [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
                permission: 'view dashboard',
            },
        ];

        if (!user) return items;

        const userRoles = user.role_names as string[] || [];
        const userPermissions = user.permission_names as string[] || [];

        // Content Management Section (Author+)
        if (userRoles.some(role => ['Super Admin', 'Admin', 'Editor', 'Author'].includes(role))) {
            items.push({
                title: 'Posts',
                href: '/content/posts',
                icon: FileText,
                permission: 'view posts',
            });

            if (userPermissions.includes('upload media')) {
                items.push({
                    title: 'Media',
                    href: '/content/media',
                    icon: Image,
                    permission: 'upload media',
                });
            }
        }

        // Admin Section (Admin+)
        if (userRoles.some(role => ['Super Admin', 'Admin'].includes(role))) {
            if (userPermissions.includes('view users')) {
                items.push({
                    title: 'Users',
                    href: '/admin/users',
                    icon: Users,
                    permission: 'view users',
                });
            }

            if (userPermissions.includes('manage settings')) {
                items.push({
                    title: 'Admin Settings',
                    href: '/admin/settings',
                    icon: Settings,
                    permission: 'manage settings',
                });
            }
        }

        // Super Admin Only
        if (userRoles.includes('Super Admin')) {
            items.push({
                title: 'Roles & Permissions',
                href: '/admin/roles',
                icon: Shield,
                role: 'Super Admin',
            });
        }

        return items;
    };

    const mainNavItems = getNavItems();

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
