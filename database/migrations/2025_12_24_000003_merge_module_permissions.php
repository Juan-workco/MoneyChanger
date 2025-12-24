<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Permission;
use App\Role;
use Illuminate\Support\Facades\DB;

class MergeModulePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define the merge map
        $merges = [
            'manage_currencies' => [
                'old' => ['create_currencies', 'edit_currencies'],
                'name' => 'Manage Currencies',
                'description' => 'Create and edit currencies'
            ],
            'manage_exchange_rates' => [
                'old' => ['create_exchange_rates', 'edit_exchange_rates'],
                'name' => 'Manage Exchange Rates',
                'description' => 'Create and edit exchange rates'
            ]
        ];

        foreach ($merges as $newSlug => $config) {
            // 1. Create the new permission if it doesn't exist
            $newPerm = Permission::firstOrCreate(
                ['slug' => $newSlug],
                ['name' => $config['name'], 'description' => $config['description']]
            );

            // 2. Find roles that have any of the old permissions and give them the new one
            $oldPermIds = Permission::whereIn('slug', $config['old'])->pluck('id')->toArray();

            if (!empty($oldPermIds)) {
                $roleIds = DB::table('permission_role')
                    ->whereIn('permission_id', $oldPermIds)
                    ->pluck('role_id')
                    ->unique();

                foreach ($roleIds as $roleId) {
                    $exists = DB::table('permission_role')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $newPerm->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('permission_role')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $newPerm->id
                        ]);
                    }
                }

                // 3. Delete old permissions and their role associations
                DB::table('permission_role')->whereIn('permission_id', $oldPermIds)->delete();
                Permission::whereIn('id', $oldPermIds)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-creating the old ones would be complex without knowing original IDs, 
        // but we can just recreate them based on the same slugs.
        $permissions = [
            ['name' => 'Create Currencies', 'slug' => 'create_currencies', 'description' => 'Add new currencies'],
            ['name' => 'Edit Currencies', 'slug' => 'edit_currencies', 'description' => 'Edit existing currencies'],
            ['name' => 'Create Exchange Rates', 'slug' => 'create_exchange_rates', 'description' => 'Add new exchange rates'],
            ['name' => 'Edit Exchange Rates', 'slug' => 'edit_exchange_rates', 'description' => 'Edit existing exchange rates'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // We don't delete the 'manage' ones to avoid breaking data if someone uses them.
    }
}
