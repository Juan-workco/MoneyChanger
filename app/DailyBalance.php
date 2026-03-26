<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyBalance extends Model
{
    protected $fillable = [
        'balance_date',
        'account_type',
        'account_id',
        'currency_id',
        'closing_balance',
    ];

    protected $dates = [
        'balance_date',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
