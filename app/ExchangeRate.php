<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_from_id',
        'currency_to_id',
        'buy_rate',
        'sell_rate',
        'effective_date',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'buy_rate' => 'decimal:2',
        'sell_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'effective_date',
    ];

    /**
     * Get the source currency
     */
    public function currencyFrom()
    {
        return $this->belongsTo(Currency::class, 'currency_from_id');
    }

    /**
     * Get the target currency
     */
    public function currencyTo()
    {
        return $this->belongsTo(Currency::class, 'currency_to_id');
    }

    /**
     * Get the user who created this rate
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get transactions using this exchange rate
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope to get only active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get rates for a specific currency pair
     */
    public function scopeForPair($query, $fromId, $toId)
    {
        return $query->where('currency_from_id', $fromId)
            ->where('currency_to_id', $toId);
    }

    /**
     * Get the current active rate for a currency pair
     */
    public static function getActiveRate($fromId, $toId)
    {
        return self::active()
            ->forPair($fromId, $toId)
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Calculate profit for a given amount
     */
    public function calculateProfit($amount)
    {
        return ($this->sell_rate - $this->buy_rate) * $amount;
    }
}
