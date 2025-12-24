<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_currencies')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view currencies.');
        }

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
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to manage currencies.');
        }

        return view('currencies.create');
    }

    /**
     * Store a newly created currency
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to manage currencies.');
        }

        $validated = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $currency = Currency::create($validated);

        ActivityLogService::log('currency_created', "Created currency {$currency->code} - {$currency->name}", $currency);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency created successfully');
    }

    /**
     * Show the form for editing the specified currency
     */
    public function edit($id)
    {
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to manage currencies.');
        }

        $currency = Currency::findOrFail($id);
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to manage currencies.');
        }

        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,' . $id,
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $currency->update($validated);

        ActivityLogService::log('currency_updated', "Updated currency {$currency->code}", $currency);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency updated successfully');
    }

    /**
     * Remove the specified currency
     */
    public function destroy($id)
    {
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to delete currencies.');
        }

        $currency = Currency::findOrFail($id);

        // Check if currency is used in exchange rates or transactions
        if ($currency->exchangeRatesFrom()->count() > 0 || $currency->exchangeRatesTo()->count() > 0) {
            return redirect()->route('currencies.index')
                ->with('error', 'Cannot delete currency that is used in exchange rates');
        }

        $code = $currency->code;
        $currency->delete();

        ActivityLogService::log('currency_deleted', "Deleted currency $code");

        return redirect()->route('currencies.index')
            ->with('success', 'Currency deleted successfully');
    }

    /**
     * Activate a currency
     */
    public function activate($id)
    {
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to manage currencies.');
        }

        $currency = Currency::findOrFail($id);
        $currency->update(['is_active' => true]);

        ActivityLogService::log('currency_activated', "Activated currency {$currency->code}", $currency);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency activated successfully');
    }

    /**
     * Deactivate a currency
     */
    public function deactivate($id)
    {
        if (!auth()->user()->hasPermission('manage_currencies')) {
            return redirect()->route('currencies.index')->with('error', 'You do not have permission to manage currencies.');
        }

        $currency = Currency::findOrFail($id);
        $currency->update(['is_active' => false]);

        ActivityLogService::log('currency_deactivated', "Deactivated currency {$currency->code}", $currency);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency deactivated successfully');
    }
}
