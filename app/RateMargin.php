<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RateMargin extends Model
{
    protected $fillable = [
        'currency_pair_id',
        'buy_markup',
        'sell_markup',
        'auto_apply',
    ];

    public function currencyPair()
    {
        return $this->belongsTo(CurrencyPair::class);
    }
}
