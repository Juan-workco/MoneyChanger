<?php

namespace App\Http\Controllers;

use App\CurrencyPair;
use App\Currency;
use Illuminate\Http\Request;

class CurrencyPairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencyPairs = CurrencyPair::with(['baseCurrency', 'targetCurrency'])->get();
        $currencies = Currency::active()->orderBy('code')->get();

        return view('currency-pairs.index', compact('currencyPairs', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'base_currency_id' => 'required|exists:currencies,id',
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'default_point' => 'required|numeric|min:0',
        ]);

        // Check uniqueness manually for compound key
        $exists = CurrencyPair::where('base_currency_id', $validated['base_currency_id'])
            ->where('target_currency_id', $validated['target_currency_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['base_currency_id' => 'This currency pair already exists.'])->withInput();
        }

        $pair = CurrencyPair::create($validated);

        return redirect()->route('currency-pairs.index')
            ->with('success', 'Currency Pair added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pair = CurrencyPair::findOrFail($id);
        $pair->delete();

        return redirect()->route('currency-pairs.index')
            ->with('success', 'Currency Pair deleted successfully');
    }

    /**
     * Toggle commission status
     */
    public function toggleCommission($id)
    {
        // Assuming manual permission check or use middleware
        if (!auth()->user()->hasPermission('manage_settings')) { // Using manage_settings as it replaces settings logic
            return back()->with('error', 'Permission denied.');
        }

        $pair = CurrencyPair::findOrFail($id);
        $pair->is_commission_enabled = !$pair->is_commission_enabled;
        $pair->save();

        return back()->with('success', 'Commission status updated for ' . $pair->baseCurrency->code . '/' . $pair->targetCurrency->code);
    }
}
