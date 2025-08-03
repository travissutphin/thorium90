import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useState } from 'react';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: string[];
}

interface PermissionMatrixProps {
    roles: Role[];
    permissions: Record<string, Permission[]>;
    selectedPermissions: string[];
    onPermissionChange: (permission: string, checked: boolean) => void;
    readOnly?: boolean;
}

export function PermissionMatrix({ 
    roles, 
    permissions, 
    selectedPermissions, 
    onPermissionChange,
    readOnly = false 
}: PermissionMatrixProps) {
    const [expandedGroups, setExpandedGroups] = useState<Record<string, boolean>>({});

    const toggleGroup = (groupName: string) => {
        setExpandedGroups(prev => ({
            ...prev,
            [groupName]: !prev[groupName]
        }));
    };

    const selectAllInGroup = (groupPermissions: Permission[], checked: boolean) => {
        if (readOnly) return;
        
        groupPermissions.forEach(permission => {
            onPermissionChange(permission.name, checked);
        });
    };

    const isGroupFullySelected = (groupPermissions: Permission[]) => {
        return groupPermissions.every(permission => 
            selectedPermissions.includes(permission.name)
        );
    };

    const isGroupPartiallySelected = (groupPermissions: Permission[]) => {
        const selectedCount = groupPermissions.filter(permission => 
            selectedPermissions.includes(permission.name)
        ).length;
        return selectedCount > 0 && selectedCount < groupPermissions.length;
    };

    return (
        <div className="space-y-4">
            {Object.entries(permissions).map(([groupName, groupPermissions]) => {
                const isExpanded = expandedGroups[groupName] ?? true;
                const isFullySelected = isGroupFullySelected(groupPermissions);
                const isPartiallySelected = isGroupPartiallySelected(groupPermissions);

                return (
                    <Card key={groupName}>
                        <CardHeader 
                            className="cursor-pointer hover:bg-muted/50 transition-colors"
                            onClick={() => toggleGroup(groupName)}
                        >
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <CardTitle className="capitalize text-lg">
                                        {groupName}
                                    </CardTitle>
                                    <Badge variant="outline">
                                        {groupPermissions.length} permissions
                                    </Badge>
                                </div>
                                
                                <div className="flex items-center gap-3">
                                    {!readOnly && (
                                        <div className="flex items-center gap-2" onClick={(e) => e.stopPropagation()}>
                                            <Checkbox
                                                checked={isFullySelected}
                                                onCheckedChange={(checked) => 
                                                    selectAllInGroup(groupPermissions, checked as boolean)
                                                }
                                            />
                                            <span className="text-sm text-muted-foreground">
                                                Select All
                                            </span>
                                        </div>
                                    )}
                                    
                                    <div className="text-sm text-muted-foreground">
                                        {isExpanded ? '−' : '+'}
                                    </div>
                                </div>
                            </div>
                            
                            <CardDescription>
                                {groupPermissions.filter(p => selectedPermissions.includes(p.name)).length} of {groupPermissions.length} selected
                            </CardDescription>
                        </CardHeader>
                        
                        {isExpanded && (
                            <CardContent>
                                <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                    {groupPermissions.map((permission) => (
                                        <div 
                                            key={permission.id} 
                                            className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/30 transition-colors"
                                        >
                                            <Checkbox
                                                id={`permission-${permission.id}`}
                                                checked={selectedPermissions.includes(permission.name)}
                                                onCheckedChange={(checked) => 
                                                    onPermissionChange(permission.name, checked as boolean)
                                                }
                                                disabled={readOnly}
                                            />
                                            <label 
                                                htmlFor={`permission-${permission.id}`}
                                                className="flex-1 text-sm font-medium cursor-pointer"
                                            >
                                                {permission.name}
                                            </label>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        )}
                    </Card>
                );
            })}
            
            {/* Role Comparison */}
            {roles.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Role Comparison</CardTitle>
                        <CardDescription>
                            See how your selection compares to existing roles
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {roles.map((role) => {
                                const matchingPermissions = role.permissions.filter(p => 
                                    selectedPermissions.includes(p)
                                ).length;
                                const matchPercentage = role.permissions.length > 0 
                                    ? Math.round((matchingPermissions / role.permissions.length) * 100)
                                    : 0;

                                return (
                                    <div key={role.id} className="flex items-center justify-between p-3 border rounded">
                                        <div className="flex items-center gap-3">
                                            <span className="font-medium">{role.name}</span>
                                            <Badge variant="outline">
                                                {role.permissions.length} permissions
                                            </Badge>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm text-muted-foreground">
                                                {matchingPermissions}/{role.permissions.length} match
                                            </span>
                                            <Badge 
                                                variant={matchPercentage > 80 ? "default" : matchPercentage > 50 ? "secondary" : "outline"}
                                            >
                                                {matchPercentage}%
                                            </Badge>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
