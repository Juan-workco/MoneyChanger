<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'country',
        'is_active',
        'agent_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_transactions' => 'integer',
        'total_volume' => 'decimal:2',
    ];

    /**
     * Get the agent assigned to this customer
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the user who created this customer
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get all transactions for this customer
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope to get only active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Update transaction statistics
     */
    public function updateTransactionStats()
    {
        $this->total_transactions = $this->transactions()->count();
        $this->total_volume = $this->transactions()
            ->where('status', 'sent')
            ->sum('amount_from');
        $this->save();
    }
}
