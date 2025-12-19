<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceivingAccount extends Model
{
    protected $fillable = [
        'account_type',
        'account_name',
        'account_number',
        'bank_name',
        'currency',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope to get accounts by currency
     */
    public function scopeCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }
}
