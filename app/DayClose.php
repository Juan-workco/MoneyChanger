<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DayClose extends Model
{
    protected $fillable = [
        'close_date',
        'closed_by',
        'status',
        'notes',
    ];

    protected $dates = [
        'close_date',
    ];

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
