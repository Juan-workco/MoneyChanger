<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LogController;

use App\Http\Controllers\Helper;
use Auth;
use Log;

class LogViewController extends Controller
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
        Helper::checkUAC('system.accounts.all');

        $type = Auth::user()->type;

        if($type == 'c')
            Helper::checkUAC('permissions.admin_log');
        else if($type == 'm')
            Helper::checkUAC('system.accounts.subaccount');

        return view('log');
    }

    public static function getList(Request $request)
    {
        try
        {
            Helper::checkUAC('system.accounts.all');
            
            $type = Auth::user()->type;

            if($type == 'c')
            {
                Helper::checkUAC('permissions.admin_log');
                $data = LogController::getAllList($request);
            }  
            else if($type == 'm')
            {
                Helper::checkUAC('system.accounts.subaccount');
                $data = LogController::getMerchantList($request);
            }

            return $data;
        } 
        catch (\Exception $e) 
        {
            return [];
        }
    }
}
