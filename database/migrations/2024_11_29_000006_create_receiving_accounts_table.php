<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceivingAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiving_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('account_type', ['bank', 'usdt', 'other']);
            $table->string('account_name', 200);
            $table->string('account_number', 200);
            $table->string('bank_name', 200)->nullable();
            $table->string('currency', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receiving_accounts');
    }
}
