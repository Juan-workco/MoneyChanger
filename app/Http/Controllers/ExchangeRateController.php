<?php

namespace App\Http\Controllers;

use App\ExchangeRate;
use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;
use Carbon\Carbon;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of currency pairs with their monthly rates
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('view_exchange_rates')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view exchange rates.');
        }

        $pairs = \App\CurrencyPair::with(['baseCurrency', 'targetCurrency'])
            ->join('currencies', 'currency_pairs.base_currency_id', '=', 'currencies.id')
            ->select('currency_pairs.*')
            ->orderBy('currencies.name')
            ->get();

        // Attach current monthly rate
        $currentMonth = date('Y-m');
        foreach ($pairs as $pair) {
            $pair->currentMonthlyRate = \App\MonthlyRate::where('currency_pair_id', $pair->id)
                ->where('month', $currentMonth)
                ->first();

            // Attach latest active exchange rate
            $pair->activeRate = ExchangeRate::where('currency_from_id', $pair->base_currency_id)
                ->where('currency_to_id', $pair->target_currency_id)
                ->where('is_active', true)
                ->orderBy('effective_date', 'desc')
                ->first();
        }

        return view('exchange-rates.index', compact('pairs', 'currentMonth'));
    }

    /**
     * Show historical rates for a specific currency pair
     */
    public function history(Request $request, $pairId)
    {
        if (!auth()->user()->hasPermission('view_exchange_rates')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view exchange rates.');
        }

        $pair = \App\CurrencyPair::findOrFail($pairId);

        $query = ExchangeRate::where('currency_from_id', $pair->base_currency_id)
            ->where('currency_to_id', $pair->target_currency_id)
            ->with(['creator']);

        // Filter by active status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $rates = $query->orderBy('effective_date', 'desc')->paginate(20);

        return view('exchange-rates.history', compact('pair', 'rates'));
    }

    /**
     * Store or Update Monthly Fixed Rate
     */
    public function storeMonthlyRate(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return back()->with('error', 'Permission denied.');
        }

        $validated = $request->validate([
            'currency_pair_id' => 'required|exists:currency_pairs,id',
            'month' => 'required|date_format:Y-m',
            'rate' => 'required|numeric|min:0'
        ]);

        \App\MonthlyRate::updateOrCreate(
            [
                'currency_pair_id' => $validated['currency_pair_id'],
                'month' => $validated['month']
            ],
            [
                'rate' => $validated['rate'],
                'created_by' => Auth::id()
            ]
        );

        return back()->with('success', 'Monthly rate updated successfully.');
    }

    /**
     * Show the form for creating a new exchange rate
     */
    public function create()
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return redirect()->route('exchange-rates.index')->with('error', 'You do not have permission to manage exchange rates.');
        }

        $currencies = Currency::active()->orderBy('code')->get();
        // We might want to pass pairs here too if we want to select by pair instead of individual currencies
        $pairs = \App\CurrencyPair::with(['baseCurrency', 'targetCurrency'])->get();
        $defaultCurrency = \App\SystemSetting::get('default_currency', 'MYR');

        return view('exchange-rates.create', compact('currencies', 'pairs', 'defaultCurrency'));
    }

    /**
     * Store a newly created exchange rate
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return redirect()->route('exchange-rates.index')->with('error', 'You do not have permission to manage exchange rates.');
        }

        $validated = $request->validate([
            'currency_from_id' => 'required|exists:currencies,id',
            'currency_to_id' => 'required|exists:currencies,id|different:currency_from_id',
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0|gte:buy_rate',
            'effective_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['effective_date'] = Carbon::parse($request->effective_date);

        $rate = ExchangeRate::create($validated);

        ActivityLogService::log('exchange_rate_created', "Created exchange rate for {$rate->currencyFrom->code}/{$rate->currencyTo->code}", $rate);

        // Redirect to history for this pair
        $pair = \App\CurrencyPair::where('base_currency_id', $validated['currency_from_id'])
            ->where('target_currency_id', $validated['currency_to_id'])
            ->first();

        if ($pair) {
            return redirect()->route('exchange-rates.history', $pair->id)
                ->with('success', 'Exchange rate created successfully');
        }

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Exchange rate created successfully');
    }

    /**
     * Show the form for editing the specified exchange rate
     */
    public function edit($id)
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return redirect()->route('exchange-rates.index')->with('error', 'You do not have permission to edit exchange rates.');
        }

        $exchangeRate = ExchangeRate::findOrFail($id);
        $currencies = Currency::active()->orderBy('code')->get();
        return view('exchange-rates.edit', compact('exchangeRate', 'currencies'));
    }

    /**
     * Update the specified exchange rate
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return redirect()->route('exchange-rates.index')->with('error', 'You do not have permission to edit exchange rates.');
        }

        $rate = ExchangeRate::findOrFail($id);

        $validated = $request->validate([
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0|gte:buy_rate',
            'effective_date' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['effective_date'] = Carbon::parse($request->effective_date);

        $rate->update($validated);

        ActivityLogService::log('exchange_rate_updated', "Updated exchange rate for {$rate->currencyFrom->code}/{$rate->currencyTo->code}", $rate);

        // Redirect to history
        $pair = \App\CurrencyPair::where('base_currency_id', $rate->currency_from_id)
            ->where('target_currency_id', $rate->currency_to_id)
            ->first();

        if ($pair) {
            return redirect()->route('exchange-rates.history', $pair->id)
                ->with('success', 'Exchange rate updated successfully');
        }

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Exchange rate updated successfully');
    }

    /**
     * Remove the specified exchange rate
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return redirect()->route('exchange-rates.index')->with('error', 'You do not have permission to delete exchange rates.');
        }

        $rate = ExchangeRate::findOrFail($id);
        $pairObj = \App\CurrencyPair::where('base_currency_id', $rate->currency_from_id)
            ->where('target_currency_id', $rate->currency_to_id)
            ->first();

        // Check if rate is used in transactions
        if ($rate->transactions()->count() > 0) {
            return back()->with('error', 'Cannot delete exchange rate that is used in transactions');
        }

        $pair = $rate->currencyFrom->code . '/' . $rate->currencyTo->code;
        $rate->delete();

        ActivityLogService::log('exchange_rate_deleted', "Deleted exchange rate for $pair");

        if ($pairObj) {
            return redirect()->route('exchange-rates.history', $pairObj->id)
                ->with('success', 'Exchange rate deleted successfully');
        }

        return redirect()->route('exchange-rates.index')
            ->with('success', 'Exchange rate deleted successfully');
    }

    /**
     * Get active rate for a currency pair (AJAX)
     */
    public function getActiveRate(Request $request)
    {
        $fromId = $request->currency_from_id;
        $toId = $request->currency_to_id;

        $rate = ExchangeRate::getActiveRate($fromId, $toId);

        if ($rate) {
            return response()->json([
                'success' => true,
                'rate' => $rate
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No active rate found for this currency pair'
        ], 404);
    }
}
