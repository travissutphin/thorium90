<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Super Admin']);
    }

    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users()->count(),
                'created_at' => $role->created_at->toISOString(),
                'updated_at' => $role->updated_at->toISOString(),
            ];
        });

        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'general';
        })->map(function ($group) {
            return $group->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ];
            })->values();
        });

        return Inertia::render('admin/roles/index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'general';
        })->map(function ($group) {
            return $group->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ];
            })->values();
        });

        return Inertia::render('admin/roles/create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        if (!empty($validated['permissions'])) {
            $role->givePermissionTo($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' created successfully with " . count($validated['permissions']) . " permissions.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'general';
        })->map(function ($group) {
            return $group->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                ];
            })->values();
        });

        $roleData = [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'users_count' => $role->users()->count(),
            'created_at' => $role->created_at->toISOString(),
            'updated_at' => $role->updated_at->toISOString(),
        ];

        return Inertia::render('admin/roles/edit', [
            'role' => $roleData,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();
        $addedPermissions = $request->getAddedPermissions();
        $removedPermissions = $request->getRemovedPermissions();

        // Update role name if changed
        if ($request->isNameChanging()) {
            $role->update([
                'name' => $validated['name'],
            ]);
        }

        // Sync permissions
        $role->syncPermissions($validated['permissions']);

        // Build success message with details
        $message = "Role '{$role->name}' updated successfully.";
        if (!empty($addedPermissions) || !empty($removedPermissions)) {
            $details = [];
            if (!empty($addedPermissions)) {
                $details[] = count($addedPermissions) . " permissions added";
            }
            if (!empty($removedPermissions)) {
                $details[] = count($removedPermissions) . " permissions removed";
            }
            $message .= " (" . implode(', ', $details) . ")";
        }

        return redirect()->route('admin.roles.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of Super Admin role
        if ($role->name === 'Super Admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete Super Admin role.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role that has assigned users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
