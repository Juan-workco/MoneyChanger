<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionModuleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create currency_pairs table
        Schema::create('currency_pairs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('base_currency_id');
            $table->unsignedInteger('target_currency_id');
            $table->decimal('default_point', 10, 4)->default(0); // Global default point
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicate pairs
            $table->unique(['base_currency_id', 'target_currency_id']);

            // Foreign keys
            $table->foreign('base_currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('target_currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });

        // 2. Create customer_upline_commissions table
        Schema::create('customer_upline_commissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('currency_pair_id');
            $table->enum('upline_level', ['upline1', 'upline2']);
            $table->decimal('point_value', 10, 4)->default(0); // Specific point for customer/pair/upline
            $table->timestamps();

            // Unique constraint: one config per customer per pair per upline level
            $table->unique(['customer_id', 'currency_pair_id', 'upline_level'], 'cust_pair_upline_unique');

            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('currency_pair_id')->references('id')->on('currency_pairs')->onDelete('cascade');
        });

        // 3. Add commission columns to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('currency_pair_id')->nullable()->after('customer_id');

            // Upline 1 Commission Data
            $table->decimal('upline1_commission_amount', 15, 2)->default(0)->after('profit_amount');
            $table->decimal('upline1_point', 10, 4)->nullable()->after('upline1_commission_amount');

            // Upline 2 Commission Data
            $table->decimal('upline2_commission_amount', 15, 2)->default(0)->after('upline1_point');
            $table->decimal('upline2_point', 10, 4)->nullable()->after('upline2_commission_amount');

            // Foreign key for link back to logic (optional but good for integrity)
            $table->foreign('currency_pair_id')->references('id')->on('currency_pairs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop columns from transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['currency_pair_id']);
            $table->dropColumn([
                'currency_pair_id',
                'upline1_commission_amount',
                'upline1_point',
                'upline2_commission_amount',
                'upline2_point'
            ]);
        });

        // Drop tables
        Schema::dropIfExists('customer_upline_commissions');
        Schema::dropIfExists('currency_pairs');
    }
}
