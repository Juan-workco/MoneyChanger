<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this currency
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get exchange rates where this currency is the source
     */
    public function exchangeRatesFrom()
    {
        return $this->hasMany(ExchangeRate::class, 'currency_from_id');
    }

    /**
     * Get exchange rates where this currency is the target
     */
    public function exchangeRatesTo()
    {
        return $this->hasMany(ExchangeRate::class, 'currency_to_id');
    }

    /**
     * Scope to get only active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
