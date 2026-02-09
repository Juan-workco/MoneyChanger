<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    protected $fillable = [
        'user_id',
        'currency_pair',
        'points'
    ];

    protected $casts = [
        'points' => 'decimal:6'
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
