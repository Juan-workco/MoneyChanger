<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\ActivityLogService;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $currentUser = auth()->user();
        $query = User::with('assignedRole');

        if ($currentUser->isSuperAdminUser()) {
            // Show all
        } elseif ($currentUser->isSuperAdminRole()) {
            // Show all except Super Admin User and Super Admin Role (unless self)
            $query->where(function ($q) use ($currentUser) {
                // Include self
                $q->where('id', $currentUser->id)
                    ->orWhere(function ($subQ) {
                        // Exclude Super Admin User
                        $subQ->where('super_admin', '!=', 1)
                            // Exclude Super Admin Role
                            ->whereDoesntHave('assignedRole', function ($roleQ) {
                            $roleQ->where('slug', 'super-admin');
                        });
                    });
            });
        } elseif ($currentUser->isAdminRole()) {
            // Show only Agents and Self
            $query->where(function ($q) use ($currentUser) {
                $q->where('id', $currentUser->id)
                    ->orWhereHas('assignedRole', function ($roleQ) {
                        $roleQ->where('slug', 'agent');
                    });
            });
        } else {
            // Default: show only self
            $query->where('id', $currentUser->id);
        }

        $users = $query->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $roles = auth()->user()->allowedRoles()->sortBy('name')->values();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        // Verify if the user is allowed to assign this role
        $allowedRoles = auth()->user()->allowedRoles();
        if (!$allowedRoles->contains('id', $validated['role_id'])) {
            return redirect()->back()->with('error', 'You are not authorized to assign this role.');
        }

        $validated['password'] = Hash::make($validated['password']);

        // Set legacy role column based on role slug for backward compatibility
        $role = Role::find($validated['role_id']);
        $validated['role'] = ($role->slug === 'agent') ? 'agent' : 'admin';

        $user = User::create($validated);

        ActivityLogService::log('user_created', "Created user {$user->username} ({$user->name})", $user);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (!auth()->user()->canManageUser($user)) {
            return redirect()->route('users.index')->with('error', 'You are not authorized to edit this user.');
        }

        $roles = auth()->user()->allowedRoles()->sortBy('name')->values();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!auth()->user()->canManageUser($user)) {
            return redirect()->route('users.index')->with('error', 'You are not authorized to edit this user.');
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['commission_rate'] = $validated['commission_rate'] ?? 0;

        // Verify role assignment permission
        $allowedRoles = auth()->user()->allowedRoles();
        if (!$allowedRoles->contains('id', $validated['role_id'])) {
            // If role changed, verify permission
            if ($user->role_id != $validated['role_id']) {
                return redirect()->back()->with('error', 'You are not authorized to assign this role.');
            }
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Set legacy role column
        $role = Role::find($validated['role_id']);
        $validated['role'] = ($role->slug === 'agent') ? 'agent' : 'admin';

        $user->update($validated);

        ActivityLogService::log('user_updated', "Updated user {$user->username} ({$user->name})", $user);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $user = User::findOrFail($id);

        if (!auth()->user()->canManageUser($user)) {
            return redirect()->route('users.index')->with('error', 'You are not authorized to delete this user.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
