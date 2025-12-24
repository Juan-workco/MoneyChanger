<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Permission;
use App\Role;

class AddModulePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            // Currencies
            ['name' => 'View Currencies', 'slug' => 'view_currencies', 'description' => 'View currency list'],
            ['name' => 'Create Currencies', 'slug' => 'create_currencies', 'description' => 'Add new currencies'],
            ['name' => 'Edit Currencies', 'slug' => 'edit_currencies', 'description' => 'Edit existing currencies'],

            // Exchange Rates
            ['name' => 'View Exchange Rates', 'slug' => 'view_exchange_rates', 'description' => 'View exchange rates'],
            ['name' => 'Create Exchange Rates', 'slug' => 'create_exchange_rates', 'description' => 'Add new exchange rates'],
            ['name' => 'Edit Exchange Rates', 'slug' => 'edit_exchange_rates', 'description' => 'Edit existing exchange rates'],
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
            'view_currencies',
            'create_currencies',
            'edit_currencies',
            'view_exchange_rates',
            'create_exchange_rates',
            'edit_exchange_rates'
        ];

        Permission::whereIn('slug', $slugs)->delete();
    }
}
