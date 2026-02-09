<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerUplineCommission extends Model
{
    protected $fillable = [
        'customer_id',
        'currency_pair_id',
        'upline_level',
        'point_value'
    ];

    protected $casts = [
        'point_value' => 'decimal:4'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currencyPair()
    {
        return $this->belongsTo(CurrencyPair::class);
    }
}
