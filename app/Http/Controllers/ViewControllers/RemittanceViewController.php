<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\RemittanceController;
use Log;

class RemittanceViewController extends Controller
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
        return view('remittance.remittance_list');
    }

    public function create()
    {
        $customers = RemittanceController::getCustomers();
        $currencies = RemittanceController::getCurrencies();
        $defaultCurrencies = RemittanceController::getDefaultCurrencies();

        return view('remittance.remittance_new')
                ->with([
                    'customers' => $customers,
                    'currencies' => $currencies,
                    'defaultCurrency' => $defaultCurrencies
                ]);
    }

    public function getList(Request $request)
    {
        return RemittanceController::getList($request);
    }

    public function createRemittance(Request $request)
    {
        return RemittanceController::createRemittance($request);
    }
}
