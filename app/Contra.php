<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contra extends Model
{
    protected $fillable = [
        'contra_code',
        'customer_id',
        'currency_a_id',
        'amount_a',
        'currency_b_id',
        'amount_b',
        'exchange_rate',
        'notes',
        'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currencyA()
    {
        return $this->belongsTo(Currency::class, 'currency_a_id');
    }

    public function currencyB()
    {
        return $this->belongsTo(Currency::class, 'currency_b_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
