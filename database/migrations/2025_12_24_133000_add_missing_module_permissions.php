<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Permission;
use App\Role;

class AddMissingModulePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            // Customers
            ['name' => 'View Customers', 'slug' => 'view_customers', 'description' => 'View customer list'],
            ['name' => 'Manage Customers', 'slug' => 'manage_customers', 'description' => 'Create and edit customers'],

            // Transactions
            ['name' => 'View Transactions', 'slug' => 'view_transactions', 'description' => 'View transaction list'],
            ['name' => 'Manage Transactions', 'slug' => 'manage_transactions', 'description' => 'Create and edit transactions'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Assign to Super Admin
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $allPermissions = Permission::all();
            $superAdmin->permissions()->sync($allPermissions);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $slugs = [
            'view_customers',
            'manage_customers',
            'view_transactions',
            'manage_transactions'
        ];

        Permission::whereIn('slug', $slugs)->delete();
    }
}
