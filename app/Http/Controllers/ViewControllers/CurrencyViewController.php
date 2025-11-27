<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\CurrencyController;

class CurrencyViewController extends Controller
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
        // Helper::checkUAC('system.accounts.admin');
        // Helper::checkUAC('permissions.view_admin_list');

        return view('currency.list');
    }
    
    public function new()
    {
        // Helper::checkUAC('system.accounts.admin');
        // Helper::checkUAC('permissions.view_admin_list');

        return view('currency.new');
    }

    public function getList(Request $request)
    {
        return CurrencyController::getList($request);
    }

    public function createCurrency(Request $request)
    {
        return CurrencyController::createCurrency($request);
    }

    public function edit(Request $request)
    {
        return CurrencyController::edit($request);
    }
}
