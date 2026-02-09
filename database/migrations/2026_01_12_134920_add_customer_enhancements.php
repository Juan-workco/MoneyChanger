<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerEnhancements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add new columns to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('group_name', 100)->nullable()->after('name');
            $table->string('contact_info', 255)->nullable()->after('email');
            $table->unsignedInteger('upline1_id')->nullable()->after('agent_id');
            $table->unsignedInteger('upline2_id')->nullable()->after('upline1_id');

            $table->foreign('upline1_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('upline2_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['upline1_id']);
            $table->dropForeign(['upline2_id']);
            $table->dropColumn(['group_name', 'contact_info', 'upline1_id', 'upline2_id']);
        });
    }
}
