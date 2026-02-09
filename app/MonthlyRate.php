<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MonthlyRate extends Model
{
    protected $fillable = [
        'currency_pair_id',
        'month', // YYYY-MM
        'rate',
        'created_by'
    ];

    protected $casts = [
        'rate' => 'decimal:8',
    ];

    public function currencyPair()
    {
        return $this->belongsTo(CurrencyPair::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
