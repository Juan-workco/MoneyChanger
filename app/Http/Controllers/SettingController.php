<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Log;

class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public static function getSetting()
    {
        $setting = DB::SELECT("
            SELECT setting_key, setting_value
            FROM system_settings
        ");

        if (!empty($setting)) {
            return $setting;
        }

        return [];
    }

    public static function updateSetting(Request $request)
    {
        try
        {
            foreach($request->all() as $key => $value)
            {
                if ($key == '_token') continue;

                // DB::UPDATE("
                //     UPDATE system_settings
                //     SET setting_value = ?
                //     WHERE setting_key = ?
                // ", [$value, $key]);

                DB::INSERT("
                    INSERT INTO system_settings (`setting_key`, `setting_value`, `created_at`)
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                        `setting_key` = VALUES(`setting_key`),
                        `setting_value` = VALUES(`setting_value`),
                        `updated_at` = NOW()
                ", [$key, $value]);
            }

            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
        }
    }

    public static function getCurrencyOptions()
    {
        try
        {
            $db = DB::SELECT("
                SELECT id, code, name, symbol
                FROM currencies
            ");

            return $db;

        }
        catch (\Exception $e)
        {
            Log::error($e);
            return [];
        }
    }
}
