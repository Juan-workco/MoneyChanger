<?php

namespace App\Http\Controllers;

use App\RateMargin;
use App\CurrencyPair;
use Illuminate\Http\Request;

class RateMarginController extends Controller
{
    /**
     * View all rate margins for currency pairs
     */
    public function index()
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission.');
        }

        $pairs = CurrencyPair::with(['baseCurrency', 'targetCurrency'])->get();
        $rateMargins = RateMargin::all()->keyBy('currency_pair_id');

        return view('rate-margins.index', compact('pairs', 'rateMargins'));
    }

    /**
     * Batch update rate margins
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_exchange_rates')) {
            return back()->with('error', 'Permission denied.');
        }

        $validated = $request->validate([
            'margins' => 'required|array',
            'margins.*.buy_markup' => 'required|numeric',
            'margins.*.sell_markup' => 'required|numeric',
            'margins.*.auto_apply' => 'nullable|boolean',
        ]);

        foreach ($validated['margins'] as $pairId => $data) {
            RateMargin::updateOrCreate(
                ['currency_pair_id' => $pairId],
                [
                    'buy_markup' => $data['buy_markup'] ?? 0,
                    'sell_markup' => $data['sell_markup'] ?? 0,
                    'auto_apply' => isset($data['auto_apply']) ? 1 : 0,
                ]
            );
        }

        return back()->with('success', 'Rate margins updated successfully.');
    }

    /**
     * API endpoint for frontend auto-calculation
     */
    public function getMargin(Request $request)
    {
        $fromId = $request->query('from_id');
        $toId = $request->query('to_id');

        $pair = CurrencyPair::where('base_currency_id', $fromId)
            ->where('target_currency_id', $toId)->first();

        if (!$pair) {
            return response()->json(['buy_markup' => 0, 'sell_markup' => 0, 'auto_apply' => 0]);
        }

        $margin = RateMargin::where('currency_pair_id', $pair->id)->first();
        return response()->json($margin ?: ['buy_markup' => 0, 'sell_markup' => 0, 'auto_apply' => 0]);
    }
}
