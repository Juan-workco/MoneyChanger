<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_code', 50)->unique();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('currency_from_id');
            $table->unsignedInteger('currency_to_id');
            $table->unsignedInteger('exchange_rate_id');
            $table->decimal('amount_from', 15, 2);
            $table->decimal('amount_to', 15, 2);
            $table->decimal('buy_rate', 10, 6);
            $table->decimal('sell_rate', 10, 6);
            $table->string('payment_method', 100);
            $table->enum('status', ['pending', 'received', 'sent', 'cancelled'])->default('pending');
            $table->datetime('transaction_date');
            $table->unsignedInteger('agent_id')->nullable();
            $table->decimal('agent_commission', 10, 2)->default(0);
            $table->decimal('profit_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('currency_from_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('currency_to_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('exchange_rate_id')->references('id')->on('exchange_rates')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('transactions');
    }
}
