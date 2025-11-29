<?php

namespace App\Http\Controllers\ViewControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Helper;

class PaymentViewController extends Controller
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
    public function paymentMethod()
    {   
        Helper::checkUAC('system.accounts.admin');

        return view('payment.method');
    }

    public function paymentAccount()
    {
        Helper::checkUAC('system.accounts.admin');

        $methodList = PaymentController::getPaymentMethodOptions();

        $params = ['methods' => $methodList];

        return view('payment.account')->with($params);
    }

    public function getPaymentMethodList(Request $request)
    {
        return PaymentController::getPaymentMethodList($request);
    }

    public function createPaymentMethod(Request $request)
    {
        return PaymentController::createPaymentMethod($request);
    }

    public function updatePaymentMethod(Request $request)
    {
        return PaymentController::updatePaymentMethod($request);
    }

    public function getPaymentAccountList(Request $request)
    {
        return PaymentController::getPaymentAccountList($request);
    }

    public function createPaymentAccount(Request $request)
    {
        return PaymentController::createPaymentAccount($request);
    }
}
