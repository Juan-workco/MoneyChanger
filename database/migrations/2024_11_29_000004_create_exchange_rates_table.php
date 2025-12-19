<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_from_id');
            $table->unsignedInteger('currency_to_id');
            $table->decimal('buy_rate', 10, 6);
            $table->decimal('sell_rate', 10, 6);
            $table->decimal('profit_margin', 10, 6)->storedAs('sell_rate - buy_rate');
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('currency_from_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('currency_to_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
}
