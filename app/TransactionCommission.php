<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionCommission extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'amount',
        'points_used',
        'calculation_details',
        'is_manual_override'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'points_used' => 'decimal:6',
        'is_manual_override' => 'boolean'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
