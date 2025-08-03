import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface RoleBadgeProps {
    role: string;
    className?: string;
}

const roleStyles = {
    'Super Admin': 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
    'Admin': 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800',
    'Editor': 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800',
    'Author': 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800',
    'Subscriber': 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-900/20 dark:text-gray-400 dark:border-gray-800',
};

const roleDescriptions = {
    'Super Admin': 'Full system access with all permissions',
    'Admin': 'Site management and user administration',
    'Editor': 'Content creation, editing, and publishing',
    'Author': 'Create and manage own content',
    'Subscriber': 'Read-only access to content',
};

export function RoleBadge({ role, className }: RoleBadgeProps) {
    const styleClass = roleStyles[role as keyof typeof roleStyles] || roleStyles['Subscriber'];
    const description = roleDescriptions[role as keyof typeof roleDescriptions] || 'User role';

    return (
        <Badge
            variant="outline"
            className={cn(styleClass, className)}
            title={description}
        >
            {role}
        </Badge>
    );
}

interface UserRolesProps {
    roles: string[];
    maxDisplay?: number;
    className?: string;
}

export function UserRoles({ roles, maxDisplay = 2, className }: UserRolesProps) {
    const displayRoles = roles.slice(0, maxDisplay);
    const remainingCount = roles.length - maxDisplay;

    return (
        <div className={cn('flex flex-wrap gap-1', className)}>
            {displayRoles.map((role) => (
                <RoleBadge key={role} role={role} />
            ))}
            {remainingCount > 0 && (
                <Badge variant="outline" className="bg-gray-50 text-gray-600 border-gray-200">
                    +{remainingCount} more
                </Badge>
            )}
        </div>
    );
}
