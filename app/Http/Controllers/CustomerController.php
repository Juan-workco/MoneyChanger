<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::with('agent');

        // Search by name, email, phone, or ID number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        // No need to pass agents - will use logged-in user
        return view('customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'nullable|email|max:100',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['agent_id'] = Auth::id(); // Auto-assign logged-in user as agent

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully');
    }

    /**
     * Display the specified customer
     */
    public function show($id)
    {
        $customer = Customer::with([
            'transactions' => function ($q) {
                $q->orderBy('transaction_date', 'desc')->limit(50);
            }
        ])->findOrFail($id);

        $stats = [
            'total_transactions' => $customer->transactions()->count(),
            'sent_transactions' => $customer->transactions()->where('status', 'sent')->count(),
            'pending_transactions' => $customer->transactions()->where('status', 'pending')->count(),
            'total_volume' => $customer->transactions()->where('status', 'sent')->sum('amount_from'),
        ];

        return view('customers.show', compact('customer', 'stats'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        // No need to pass agents - will use logged-in user
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'nullable|email|max:100',
            'phone' => 'required|string|max:50',
            'id_type' => 'required|in:ic,passport,other',
            'id_number' => 'required|string|max:100',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check if customer has transactions
        if ($customer->transactions()->count() > 0) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing transactions');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    /**
     * Get customer transaction history
     */
    public function transactionHistory($id)
    {
        $customer = Customer::findOrFail($id);
        $transactions = $customer->transactions()
            ->with(['currencyFrom', 'currencyTo'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

            log::debug($transactions);

        return view('customers.transactions', compact('customer', 'transactions'));
    }
}
