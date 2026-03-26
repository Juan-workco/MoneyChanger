<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    protected $fillable = [
        'bot_token',
        'webhook_url',
        'default_group_id',
        'is_active',
    ];
}
