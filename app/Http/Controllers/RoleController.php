<?php

namespace App\Http\Controllers;

use App\Role;
use App\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            if (strpos($permission->slug, 'currencies') !== false)
                return 'Currencies';
            if (strpos($permission->slug, 'exchange_rates') !== false)
                return 'Exchange Rates';
            if (strpos($permission->slug, 'reports') !== false)
                return 'Reports';
            if (strpos($permission->slug, 'settings') !== false)
                return 'Settings';
            if (strpos($permission->slug, 'roles') !== false)
                return 'Roles';
            if (strpos($permission->slug, 'users') !== false)
                return 'Users';
            if (strpos($permission->slug, 'transactions') !== false)
                return 'Transactions';
            if (strpos($permission->slug, 'customers') !== false)
                return 'Customers';
            return 'Other';
        });

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles',
            'slug' => 'required|unique:roles',
        ]);

        $role = Role::create($request->only('name', 'slug', 'description'));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy(function ($permission) {
            if (strpos($permission->slug, 'currencies') !== false)
                return 'Currencies';
            if (strpos($permission->slug, 'exchange_rates') !== false)
                return 'Exchange Rates';
            if (strpos($permission->slug, 'reports') !== false)
                return 'Reports';
            if (strpos($permission->slug, 'settings') !== false)
                return 'Settings';
            if (strpos($permission->slug, 'roles') !== false)
                return 'Roles';
            if (strpos($permission->slug, 'users') !== false)
                return 'Users';
            if (strpos($permission->slug, 'transactions') !== false)
                return 'Transactions';
            if (strpos($permission->slug, 'customers') !== false)
                return 'Customers';
            return 'Other';
        });

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting super-admin role
        if ($role->slug === 'super-admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete Super Admin role');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
