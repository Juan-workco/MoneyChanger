<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTransactionsStatusEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Expand ENUM to include NEW values (keeping old ones to avoid truncation)
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'received', 'sent', 'cancelled', 'accept', 'cancel') NOT NULL DEFAULT 'pending'");

        // Step 2: Migrate data from old values to new values
        DB::table('transactions')->where('status', 'received')->update(['status' => 'accept']);
        DB::table('transactions')->where('status', 'sent')->update(['status' => 'sent']);
        DB::table('transactions')->where('status', 'cancelled')->update(['status' => 'cancel']);

        // Step 3: Restrict ENUM to ONLY new values
        // This might fail if there are still records with old values, but we just updated them.
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'accept', 'sent', 'cancel') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Step 1: Expand ENUM to include OLD values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'accept', 'sent', 'cancel', 'received', 'cancelled') NOT NULL DEFAULT 'pending'");

        // Step 2: Migrate data back
        DB::table('transactions')->where('status', 'accept')->update(['status' => 'received']);
        DB::table('transactions')->where('status', 'sent')->update(['status' => 'sent']);
        DB::table('transactions')->where('status', 'cancel')->update(['status' => 'cancelled']);

        // Step 3: Restrict ENUM to ONLY old values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'received', 'sent') NOT NULL DEFAULT 'pending'");
    }
}
