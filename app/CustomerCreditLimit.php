<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerCreditLimit extends Model
{
    protected $fillable = [
        'customer_id',
        'currency_id',
        'credit_limit',
        'enforcement',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
