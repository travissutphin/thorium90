<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:view users')->only(['index', 'show', 'trashed']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy']);
        $this->middleware('permission:restore users')->only(['restore']);
        $this->middleware('permission:force delete users')->only(['forceDelete']);
    }

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with(['roles.permissions'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'provider' => $user->provider,
                    'avatar' => $user->avatar,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')->toArray(),
                        ];
                    }),
                    'role_names' => $user->roles->pluck('name')->toArray(),
                    'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'is_social_user' => $user->isSocialUser(),
                    'avatar_url' => $user->getAvatarUrl(),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                ];
            });

        // Get user statistics
        $stats = [
            'total_users' => User::count(),
            'administrators' => User::role(['Super Admin', 'Admin'])->count(),
            'content_creators' => User::role(['Editor', 'Author'])->count(),
            'subscribers' => User::role('Subscriber')->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'social_users' => User::whereNotNull('provider')->count(),
        ];

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users()->count(),
            ];
        });

        return Inertia::render('admin/users/create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
            'email_verified' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'email_verified_at' => $request->email_verified ? now() : null,
        ]);

        if ($request->has('roles') && !empty($request->roles)) {
            $user->assignRole($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles.permissions']);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'provider' => $user->provider,
            'provider_id' => $user->provider_id,
            'avatar' => $user->avatar,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ];
            }),
            'role_names' => $user->roles->pluck('name')->toArray(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
            'is_social_user' => $user->isSocialUser(),
            'avatar_url' => $user->getAvatarUrl(),
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ];

        return Inertia::render('admin/users/show', [
            'user' => $userData,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['roles']);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'provider' => $user->provider,
            'avatar' => $user->avatar,
            'role_names' => $user->roles->pluck('name')->toArray(),
            'is_social_user' => $user->isSocialUser(),
            'avatar_url' => $user->getAvatarUrl(),
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ];

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users()->count(),
            ];
        });

        return Inertia::render('admin/users/edit', [
            'user' => $userData,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
            'email_verified' => 'boolean',
        ]);

        // Prevent removing Super Admin role from the last Super Admin
        $currentRoles = $user->roles->pluck('name')->toArray();
        $newRoles = $request->roles ?? [];

        if (in_array('Super Admin', $currentRoles) && !in_array('Super Admin', $newRoles)) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->back()
                    ->with('error', 'Cannot remove Super Admin role from the last Super Admin user.');
            }
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        // Handle email verification status
        if ($request->has('email_verified')) {
            $updateData['email_verified_at'] = $request->email_verified ? now() : null;
        }

        $user->update($updateData);

        // Sync user roles
        if ($request->has('roles')) {
            $user->syncRoles($newRoles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the last Super Admin
        if ($user->hasRole('Super Admin')) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot delete the last Super Admin user.');
            }
        }

        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete(); // This will be a soft delete now

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully. The user can be restored if needed.');
    }

    /**
     * Bulk operations on users.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,assign_role,remove_role,verify_email,unverify_email',
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $currentUserId = auth()->id();

        switch ($request->action) {
            case 'delete':
                // Prevent deletion of current user and last Super Admin
                $superAdminCount = User::role('Super Admin')->count();
                $usersToDelete = $users->filter(function ($user) use ($currentUserId, $superAdminCount) {
                    if ($user->id === $currentUserId) {
                        return false; // Can't delete self
                    }
                    if ($user->hasRole('Super Admin') && $superAdminCount <= 1) {
                        return false; // Can't delete last Super Admin
                    }
                    return true;
                });

                $deletedCount = $usersToDelete->count();
                User::whereIn('id', $usersToDelete->pluck('id'))->delete();
                
                return redirect()->back()
                    ->with('success', "Successfully deleted {$deletedCount} users.");

            case 'assign_role':
                if (!$request->role) {
                    return redirect()->back()->with('error', 'Role is required for assignment.');
                }
                
                foreach ($users as $user) {
                    $user->assignRole($request->role);
                }
                
                return redirect()->back()
                    ->with('success', "Successfully assigned {$request->role} role to " . $users->count() . " users.");

            case 'remove_role':
                if (!$request->role) {
                    return redirect()->back()->with('error', 'Role is required for removal.');
                }
                
                // Prevent removing Super Admin role from last Super Admin
                if ($request->role === 'Super Admin') {
                    $superAdminCount = User::role('Super Admin')->count();
                    $superAdminsToUpdate = $users->filter(fn($user) => $user->hasRole('Super Admin'));
                    
                    if ($superAdminCount - $superAdminsToUpdate->count() < 1) {
                        return redirect()->back()
                            ->with('error', 'Cannot remove Super Admin role - at least one Super Admin must remain.');
                    }
                }
                
                foreach ($users as $user) {
                    $user->removeRole($request->role);
                }
                
                return redirect()->back()
                    ->with('success', "Successfully removed {$request->role} role from " . $users->count() . " users.");

            case 'verify_email':
                User::whereIn('id', $request->user_ids)
                    ->whereNull('email_verified_at')
                    ->update(['email_verified_at' => now()]);
                
                return redirect()->back()
                    ->with('success', 'Successfully verified email for selected users.');

            case 'unverify_email':
                User::whereIn('id', $request->user_ids)
                    ->update(['email_verified_at' => null]);
                
                return redirect()->back()
                    ->with('success', 'Successfully unverified email for selected users.');
        }

        return redirect()->back()->with('error', 'Invalid action.');
    }

    /**
     * Display a listing of soft-deleted users.
     */
    public function trashed()
    {
        $users = User::onlyTrashed()
            ->with(['roles.permissions'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'provider' => $user->provider,
                    'avatar' => $user->avatar,
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')->toArray(),
                        ];
                    }),
                    'role_names' => $user->roles->pluck('name')->toArray(),
                    'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'is_social_user' => $user->isSocialUser(),
                    'avatar_url' => $user->getAvatarUrl(),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                    'deleted_at' => $user->deleted_at->toISOString(),
                ];
            });

        // Get deleted user statistics
        $stats = [
            'total_deleted' => User::onlyTrashed()->count(),
            'deleted_administrators' => User::onlyTrashed()->role(['Super Admin', 'Admin'])->count(),
            'deleted_content_creators' => User::onlyTrashed()->role(['Editor', 'Author'])->count(),
            'deleted_subscribers' => User::onlyTrashed()->role('Subscriber')->count(),
        ];

        return Inertia::render('admin/users/trashed', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        
        $user->restore();

        return redirect()->back()
            ->with('success', "User '{$user->name}' has been restored successfully.");
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        
        // Additional security check - only Super Admins can force delete
        if (!auth()->user()->hasRole('Super Admin')) {
            return redirect()->back()
                ->with('error', 'Only Super Admins can permanently delete users.');
        }

        $userName = $user->name;
        $user->forceDelete();

        return redirect()->back()
            ->with('success', "User '{$userName}' has been permanently deleted.");
    }
}
