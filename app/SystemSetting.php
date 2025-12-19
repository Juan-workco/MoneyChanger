<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $type = 'general')
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'setting_type' => $type]
        );
    }

    /**
     * Get all settings by type
     */
    public static function getByType($type)
    {
        return self::where('setting_type', $type)->get();
    }

    /**
     * Get payment methods
     */
    public static function getPaymentMethods()
    {
        $setting = self::get('payment_methods');
        return $setting ? json_decode($setting, true) : [];
    }

    /**
     * Set payment methods
     */
    public static function setPaymentMethods(array $methods)
    {
        return self::set('payment_methods', json_encode($methods), 'payment_method');
    }
}
