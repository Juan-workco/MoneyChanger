<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'transaction_date',
        'reference_type',
        'reference_id',
        'account_type',
        'account_id',
        'currency_id',
        'debit',
        'credit',
        'running_balance',
        'created_by',
    ];

    protected $dates = [
        'transaction_date',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
