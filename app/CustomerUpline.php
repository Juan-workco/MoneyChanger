<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerUpline extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'role'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
