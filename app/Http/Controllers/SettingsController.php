<?php

namespace App\Http\Controllers;

use App\ReceivingAccount;
use App\SystemSetting;
use App\Currency;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show settings dashboard
     */
    public function index()
    {
        $generalSettings = [
            'app_name' => SystemSetting::get('app_name', 'Money Changer Admin'),
            'default_currency' => SystemSetting::get('default_currency', 'MYR'),
        ];

        $currencies = Currency::active()->get();

        return view('settings.index', compact('generalSettings', 'currencies'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:200',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:200',
            'default_currency' => 'required|string|max:10',
        ]);

        SystemSetting::set('company_name', $validated['company_name'] ?? '');
        SystemSetting::set('company_address', $validated['company_address'] ?? '');
        SystemSetting::set('company_phone', $validated['company_phone'] ?? '');
        SystemSetting::set('company_email', $validated['company_email'] ?? '');
        SystemSetting::set('default_currency', $validated['default_currency']);

        return redirect()->route('settings.index')
            ->with('success', 'General settings updated successfully');
    }

    /**
     * Show receiving accounts management
     */
    public function accounts()
    {
        $accounts = ReceivingAccount::orderBy('account_type')->get();
        $currencies = Currency::active()->get();

        return view('settings.accounts', compact('accounts', 'currencies'));
    }

    /**
     * Store new receiving account
     */
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'account_type' => 'required|in:bank,usdt,other',
            'account_name' => 'required|string|max:200',
            'account_number' => 'required|string|max:200',
            'bank_name' => 'nullable|string|max:200',
            'currency' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        ReceivingAccount::create($validated);

        return redirect()->route('settings.accounts')
            ->with('success', 'Receiving account added successfully');
    }

    /**
     * Update receiving account
     */
    public function updateAccount(Request $request, $id)
    {
        $account = ReceivingAccount::findOrFail($id);

        $validated = $request->validate([
            'account_type' => 'required|in:bank,usdt,other',
            'account_name' => 'required|string|max:200',
            'account_number' => 'required|string|max:200',
            'bank_name' => 'nullable|string|max:200',
            'currency' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $account->update($validated);

        return redirect()->route('settings.accounts')
            ->with('success', 'Receiving account updated successfully');
    }

    /**
     * Delete receiving account
     */
    public function deleteAccount($id)
    {
        $account = ReceivingAccount::findOrFail($id);
        $account->delete();

        return redirect()->route('settings.accounts')
            ->with('success', 'Receiving account deleted successfully');
    }

    /**
     * Show payment methods management
     */
    public function paymentMethods()
    {
        $activeMethods = SystemSetting::getPaymentMethods();

        return view('settings.payment-methods', compact('activeMethods'));
    }

    /**
     * Update payment methods
     */
    public function updatePaymentMethods(Request $request)
    {
        $validated = $request->validate([
            'payment_methods' => 'required|array',
            'payment_methods.*' => 'required|string|max:100',
        ]);

        SystemSetting::setPaymentMethods($validated['payment_methods']);

        return redirect()->route('settings.payment-methods')
            ->with('success', 'Payment methods updated successfully');
    }
}
