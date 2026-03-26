<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerBalance extends Model
{
    protected $fillable = [
        'customer_id',
        'currency_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the currency
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get or create a balance record for a customer + currency
     */
    public static function getBalance($customerId, $currencyId)
    {
        $record = self::where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->first();

        return $record ? $record->balance : 0;
    }

    /**
     * Adjust (add/subtract) balance for a customer + currency
     * Positive amount = add, Negative amount = deduct
     */
    public static function adjustBalance($customerId, $currencyId, $amount)
    {
        $record = self::firstOrCreate(
            [
                'customer_id' => $customerId,
                'currency_id' => $currencyId,
            ],
            ['balance' => 0]
        );

        $record->balance += $amount;
        $record->save();

        return $record;
    }
}
