<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage user roles']);
    }

    /**
     * Show the user role management interface.
     */
    public function show(User $user)
    {
        $user->load(['roles.permissions']);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ];
            }),
            'role_names' => $user->roles->pluck('name')->toArray(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'created_at' => $user->created_at->toISOString(),
        ];

        $availableRoles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users()->count(),
            ];
        });

        return Inertia::render('admin/users/roles', [
            'user' => $userData,
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Update user roles.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        // Prevent removing Super Admin role from the last Super Admin
        $currentRoles = $user->roles->pluck('name')->toArray();
        $newRoles = $request->roles;

        if (in_array('Super Admin', $currentRoles) && !in_array('Super Admin', $newRoles)) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Cannot remove Super Admin role from the last Super Admin user.');
            }
        }

        // Sync user roles
        $user->syncRoles($newRoles);

        return redirect()->back()
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Bulk assign roles to multiple users.
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
            'action' => 'required|in:assign,remove,replace',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $roles = $request->roles;

        foreach ($users as $user) {
            switch ($request->action) {
                case 'assign':
                    $user->assignRole($roles);
                    break;
                case 'remove':
                    $user->removeRole($roles);
                    break;
                case 'replace':
                    $user->syncRoles($roles);
                    break;
            }
        }

        $actionText = [
            'assign' => 'assigned to',
            'remove' => 'removed from',
            'replace' => 'replaced for',
        ];

        return redirect()->back()
            ->with('success', "Roles {$actionText[$request->action]} " . count($users) . " users successfully.");
    }
}
