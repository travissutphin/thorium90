import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { RoleBadge } from '@/components/RoleBadge';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';

export function UserInfo({ user, showEmail = false, showRoles = false }: { user: User; showEmail?: boolean; showRoles?: boolean }) {
    const getInitials = useInitials();
    const userRoles = user.role_names as string[] || [];

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                <AvatarImage src={user.avatar} alt={user.name} />
                <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                {showEmail && <span className="truncate text-xs text-muted-foreground">{user.email}</span>}
                {showRoles && userRoles.length > 0 && (
                    <div className="flex flex-wrap gap-1 mt-1">
                        {userRoles.slice(0, 1).map((role) => (
                            <RoleBadge key={role} role={role} className="text-xs px-1 py-0 h-4" />
                        ))}
                        {userRoles.length > 1 && (
                            <span className="text-xs text-muted-foreground">+{userRoles.length - 1}</span>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}
