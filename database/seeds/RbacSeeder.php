<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;
use App\User;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // // Create Permissions
        // $permissions = [
        //     ['name' => 'View Reports', 'slug' => 'view_reports', 'description' => 'Access to view all reports'],
        //     ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Access to system settings'],
        //     ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Create and edit roles'],
        //     ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Create and edit users'],
        // ];

        // foreach ($permissions as $perm) {
        //     Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        // }

        // // Create Roles
        // $superAdminRole = Role::firstOrCreate(
        //     ['slug' => 'super-admin'],
        //     ['name' => 'Super Admin', 'description' => 'Full access to everything']
        // );

        // $agentRole = Role::firstOrCreate(
        //     ['slug' => 'agent'],
        //     ['name' => 'Agent', 'description' => 'Standard agent access']
        // );

        // // Assign Permissions to Roles
        // // Super Admin gets all permissions (though logic bypasses check, good to have)
        // $allPermissions = Permission::all();
        // $superAdminRole->permissions()->sync($allPermissions);

        // // Agent gets specific permissions (e.g., maybe none of the restricted ones initially)
        // // For now, let's say Agent can NOT view reports or settings, as per requirement.

        // // Assign Role to existing users
        // // Assign 'super-admin' to the first user (likely the dev/admin)
        // $firstUser = User::first();
        // if ($firstUser) {
        //     $firstUser->role_id = $superAdminRole->id;
        //     $firstUser->save();
        // }
    }
}
