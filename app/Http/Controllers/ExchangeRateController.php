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
     * Display a listing of exchange rates
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_exchange_rates')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view exchange rates.');
        }

        $query = ExchangeRate::with(['currencyFrom', 'currencyTo', 'creator']);

        // Filter by active status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by currency pair
        if ($request->has('currency_from') && $request->currency_from) {
            $query->where('currency_from_id', $request->currency_from);
        }
        if ($request->has('currency_to') && $request->currency_to) {
            $query->where('currency_to_id', $request->currency_to);
        }

        $rates = $query->orderBy('effective_date', 'desc')->paginate(20);
        $currencies = Currency::active()->orderBy('code')->get();

        return view('exchange-rates.index', compact('rates', 'currencies'));
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
        $defaultCurrency = \App\SystemSetting::get('default_currency', 'MYR');

        return view('exchange-rates.create', compact('currencies', 'defaultCurrency'));
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

        $rate = ExchangeRate::create($validated);

        ActivityLogService::log('exchange_rate_created', "Created exchange rate for {$rate->currencyFrom->code}/{$rate->currencyTo->code}", $rate);

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

        // Check if rate is used in transactions
        if ($rate->transactions()->count() > 0) {
            return redirect()->route('exchange-rates.index')
                ->with('error', 'Cannot delete exchange rate that is used in transactions');
        }

        $pair = $rate->currencyFrom->code . '/' . $rate->currencyTo->code;
        $rate->delete();

        ActivityLogService::log('exchange_rate_deleted', "Deleted exchange rate for $pair");

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
