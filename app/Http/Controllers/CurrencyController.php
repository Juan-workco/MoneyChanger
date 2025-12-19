<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies
     */
    public function index(Request $request)
    {
        $query = Currency::with('creator');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by code or name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $currencies = $query->orderBy('code')->paginate(20);

        return view('currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * Store a newly created currency
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:1|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Currency::create($validated);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency created successfully');
    }

    /**
     * Show the form for editing the specified currency
     */
    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency
     */
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,' . $id,
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $currency->update($validated);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency updated successfully');
    }

    /**
     * Remove the specified currency
     */
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        // Check if currency is used in exchange rates or transactions
        if ($currency->exchangeRatesFrom()->count() > 0 || $currency->exchangeRatesTo()->count() > 0) {
            return redirect()->route('currencies.index')
                ->with('error', 'Cannot delete currency that is used in exchange rates');
        }

        $currency->delete();

        return redirect()->route('currencies.index')
            ->with('success', 'Currency deleted successfully');
    }

    /**
     * Activate a currency
     */
    public function activate($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->update(['is_active' => true]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency activated successfully');
    }

    /**
     * Deactivate a currency
     */
    public function deactivate($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->update(['is_active' => false]);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency deactivated successfully');
    }
}
