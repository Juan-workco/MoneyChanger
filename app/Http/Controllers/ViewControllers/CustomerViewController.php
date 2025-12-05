<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Helper;

class CustomerViewController extends Controller
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

        return view('customer.customer_list');
    }
    
    public function getList(Request $request)
    {
        Helper::checkUAC('system.accounts.all');

        return CustomerController::getList($request);
    }
    
    public function createCustomer(Request $request)
    {
        Helper::checkUAC('system.accounts.all');

        return CustomerController::createCustomer($request);
    }
    
    public function editCustomer(Request $request)
    {
        Helper::checkUAC('system.accounts.all');

        return CustomerController::editCustomer($request);
    }
}
