<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Transaction;
use App\User;
use App\Currency;
use App\CurrencyPair;
use App\CustomerUplineCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::with('agent', 'upline1', 'upline2');

        // Filter by agent if user is an agent
        $user = Auth::user();
        if ($user->isAgent()) {
            $query->where('agent_id', $user->id);
        }

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
        // Get all agents to populate upline dropdown
        $agents = User::where('role', 'agent')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get existing group names from database
        $existingGroups = Customer::whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name')
            ->filter()
            ->values();

        // Auto-seed currency pairs if none exist (for development convenience)
        if (CurrencyPair::count() == 0) {
            $currencies = Currency::where('is_active', true)->get();
            foreach ($currencies as $base) {
                foreach ($currencies as $target) {
                    if ($base->id != $target->id) {
                        // Create pair if logic makes sense (e.g. THB-USDT)
                        // For now, create all permutations or just common ones
                        // Let's create all permutations for active currencies
                        CurrencyPair::create([
                            'base_currency_id' => $base->id,
                            'target_currency_id' => $target->id,
                            'default_point' => 0.05 // Default as per requirement example
                        ]);
                    }
                }
            }
        }

        $currencyPairs = CurrencyPair::with(['baseCurrency', 'targetCurrency'])
            ->where('is_active', true)
            ->get();

        return view('customers.create', compact('agents', 'existingGroups', 'currencyPairs'));
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'group_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'contact_info' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'upline1_id' => 'nullable|exists:users,id',
            'upline2_id' => 'nullable|exists:users,id',
            'commissions' => 'nullable|array' // New field for commissions
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['agent_id'] = Auth::id(); // Auto-assign logged-in user as agent

        DB::beginTransaction();
        try {
            $customer = Customer::create($validated);

            // Save commissions if present
            if ($request->has('commissions')) {
                foreach ($request->commissions as $pairId => $data) {
                    // Upline 1 Point
                    if (isset($data['upline1']) && $data['upline1'] !== null) {
                        CustomerUplineCommission::create([
                            'customer_id' => $customer->id,
                            'currency_pair_id' => $pairId,
                            'upline_level' => 'upline1',
                            'point_value' => $data['upline1']
                        ]);
                    }
                    // Upline 2 Point
                    if (isset($data['upline2']) && $data['upline2'] !== null) {
                        CustomerUplineCommission::create([
                            'customer_id' => $customer->id,
                            'currency_pair_id' => $pairId,
                            'upline_level' => 'upline2',
                            'point_value' => $data['upline2']
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating customer: ' . $e->getMessage())->withInput();
        }
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

        // Check permission for agents
        if (Auth::user()->isAgent() && $customer->agent_id !== Auth::id()) {
            return redirect()->route('customers.index')->with('error', 'You are not authorized to view this customer.');
        }

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
        $customer = Customer::with(['commissions'])->findOrFail($id);

        // Check permission for agents
        if (Auth::user()->isAgent() && $customer->agent_id !== Auth::id()) {
            return redirect()->route('customers.index')->with('error', 'You are not authorized to edit this customer.');
        }

        // Get all agents to populate upline dropdown
        $agents = User::where('role', 'agent')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get existing group names from database
        $existingGroups = Customer::whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name')
            ->filter()
            ->values();

        // Get currency pairs
        $currencyPairs = CurrencyPair::with(['baseCurrency', 'targetCurrency'])
            ->where('is_active', true)
            ->get();

        // Map existing commissions for easy access in view
        // Format: [pair_id => ['upline1' => val, 'upline2' => val]]
        $existingCommissions = [];
        foreach ($customer->commissions as $comm) {
            $existingCommissions[$comm->currency_pair_id][$comm->upline_level] = $comm->point_value;
        }

        return view('customers.edit', compact('customer', 'agents', 'existingGroups', 'currencyPairs', 'existingCommissions'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Check permission for agents
        if (Auth::user()->isAgent() && $customer->agent_id !== Auth::id()) {
            return redirect()->route('customers.index')->with('error', 'You are not authorized to update this customer.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'group_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'contact_info' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'upline1_id' => 'nullable|exists:users,id',
            'upline2_id' => 'nullable|exists:users,id',
            'commissions' => 'nullable|array' // New field
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        DB::beginTransaction();
        try {
            $customer->update($validated);

            // Sync commissions
            // Simplest way is delete all for this customer and re-create, or update existing
            // Let's go with updateOrInsert approach or delete-create for simplicity on MVP
            CustomerUplineCommission::where('customer_id', $customer->id)->delete();

            if ($request->has('commissions')) {
                foreach ($request->commissions as $pairId => $data) {
                    // Upline 1 Point
                    if (isset($data['upline1']) && $data['upline1'] !== null) {
                        CustomerUplineCommission::create([
                            'customer_id' => $customer->id,
                            'currency_pair_id' => $pairId,
                            'upline_level' => 'upline1',
                            'point_value' => $data['upline1']
                        ]);
                    }
                    // Upline 2 Point
                    if (isset($data['upline2']) && $data['upline2'] !== null) {
                        CustomerUplineCommission::create([
                            'customer_id' => $customer->id,
                            'currency_pair_id' => $pairId,
                            'upline_level' => 'upline2',
                            'point_value' => $data['upline2']
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        // Check permission for agents
        if (Auth::user()->isAgent() && $customer->agent_id !== Auth::id()) {
            return redirect()->route('customers.index')->with('error', 'You are not authorized to delete this customer.');
        }

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

        // Check permission for agents
        if (Auth::user()->isAgent() && $customer->agent_id !== Auth::id()) {
            return redirect()->route('customers.index')->with('error', 'You are not authorized to view this history.');
        }
        $transactions = $customer->transactions()
            ->with(['currencyFrom', 'currencyTo'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return view('customers.transactions', compact('customer', 'transactions'));
    }
}
