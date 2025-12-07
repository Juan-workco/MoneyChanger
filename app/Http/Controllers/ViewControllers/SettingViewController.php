<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\SettingController;

class SettingViewController extends Controller
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
    
    public function index()
    {
        Helper::checkUAC('system.accounts.admin');

        $settings = SettingController::getSetting();
        $currencies = SettingController::getCurrencyOptions();

        return view('setting.system')->with(['settings' => $settings, 'currencies' => $currencies]);
    }

    public function updateSetting(Request $request)
    {
        return SettingController::updateSetting($request);
    }
}
