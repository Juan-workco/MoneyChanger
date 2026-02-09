<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyPair extends Model
{
    protected $fillable = [
        'base_currency_id',
        'target_currency_id',
        'default_point',
        'is_active',
        'is_commission_enabled',
    ];

    protected $casts = [
        'default_point' => 'decimal:4',
        'is_active' => 'boolean',
        'is_commission_enabled' => 'boolean',
    ];

    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function targetCurrency()
    {
        return $this->belongsTo(Currency::class, 'target_currency_id');
    }

    // Accessor for generated name e.g. "THB-USDT"
    public function getNameAttribute()
    {
        return $this->baseCurrency->code . '-' . $this->targetCurrency->code;
    }
}
