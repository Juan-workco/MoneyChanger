<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Customer;
use App\Currency;
use App\ExchangeRate;
use App\User;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;
use Log;

class TransactionController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'currencyFrom', 'currencyTo', 'agent']);

        // Filter by agent if user is an agent
        $user = Auth::user();
        if ($user->isAgent()) {
            $query->where('created_by', $user->id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // Search by transaction code or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);
        $customers = Customer::active()->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'customers'));
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create()
    {
        $customers = Customer::active()
            ->when(Auth::user()->isAgent(), function ($query) {
                $query->where('agent_id', Auth::user()->id);
            })
            ->orderBy('name')
            ->get();


        $defaultCurrencyCode = \App\SystemSetting::get('default_currency', 'MYR');
        $defaultCurrency = Currency::where('code', $defaultCurrencyCode)->first();

        // Only show currencies that have an active exchange rate with the default currency
        $toCurrencyIds = ExchangeRate::where('is_active', true)
            ->pluck('currency_to_id');

        $currencies = Currency::whereIn('id', $toCurrencyIds)
            ->active()
            ->orderBy('code')
            ->get();

        return view('transactions.create', compact('customers', 'currencies', 'defaultCurrency'));
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'currency_from_id' => 'required|exists:currencies,id',
            'currency_to_id' => 'required|exists:currencies,id|different:currency_from_id',
            'amount_from' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get active exchange rate
            $exchangeRate = ExchangeRate::getActiveRate(
                $request->currency_from_id,
                $request->currency_to_id
            );

            if (!$exchangeRate) {
                return back()->with('error', 'No active exchange rate found for this currency pair')
                    ->withInput();
            }

            // Calculate amounts and profit
            $buyRate = $exchangeRate->buy_rate;
            $sellRate = $exchangeRate->sell_rate;
            $amountTo = $request->amount_from * $sellRate;
            $profit = $this->commissionService->calculateProfit($sellRate, $buyRate, $request->amount_from);

            $transactionData = array_merge($validated, [
                'exchange_rate_id' => $exchangeRate->id,
                'amount_to' => $amountTo,
                'buy_rate' => $buyRate,
                'sell_rate' => $sellRate,
                'profit_amount' => $profit,
                'agent_id' => Auth::user()->role === 'agent' ? Auth::id() : null,
                'agent_commission' => (Auth::user()->role === 'agent' && Auth::user()->commission_rate > 0)
                    ? ($profit * (Auth::user()->commission_rate / 100))
                    : 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            $transaction = Transaction::create($transactionData);

            // Log activity
            ActivityLogService::log('transaction_created', "Created transaction {$transaction->transaction_code} for customer " . $transaction->customer->name, $transaction);

            DB::commit();

            return redirect()->route('transactions.show', $transaction->id)
                ->with('success', 'Transaction created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating transaction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified transaction
     */
    public function show($id)
    {
        $transaction = Transaction::with([
            'customer',
            'currencyFrom',
            'currencyTo',
            'exchangeRate',
            'agent',
            'creator'
        ])->findOrFail($id);

        // Check permission for agents
        if (Auth::user()->isAgent() && $transaction->created_by !== Auth::id()) {
            return redirect()->route('transactions.index')->with('error', 'You are not authorized to view this transaction.');
        }

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);
        $customers = Customer::active()->orderBy('name')->get();
        $currencies = Currency::active()->orderBy('code')->get();

        return view('transactions.edit', compact('transaction', 'customers', 'currencies'));
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount_from' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Recalculate amounts if amount changed
        if ($request->amount_from != $transaction->amount_from) {
            $validated['amount_to'] = $request->amount_from * $transaction->sell_rate;
            $profit = $this->commissionService->calculateProfit(
                $transaction->sell_rate,
                $transaction->buy_rate,
                $request->amount_from
            );
            $validated['profit_amount'] = $profit;

            // Recalculate commission if transaction belongs to an agent
            if ($transaction->agent_id) {
                $agent = $transaction->agent;
                if ($agent && $agent->commission_rate > 0) {
                    $validated['agent_commission'] = $profit * ($agent->commission_rate / 100);
                } else {
                    $validated['agent_commission'] = 0;
                }
            }
        }

        $transaction->update($validated);

        // Log activity
        ActivityLogService::log('transaction_updated', "Updated transaction {$transaction->transaction_code}", $transaction);

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', 'Transaction updated successfully');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully');
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,accept,sent,cancel',
        ]);

        $oldStatus = $transaction->status;
        $transaction->updateStatus($request->status);

        // Log activity
        ActivityLogService::log('transaction_status_updated', "Updated transaction {$transaction->transaction_code} status from $oldStatus to {$request->status}", $transaction);

        return redirect()->back()
            ->with('success', 'Transaction status updated successfully');
    }

    /**
     * Search transactions (AJAX)
     */
    public function search(Request $request)
    {
        $query = Transaction::with(['customer', 'currencyFrom', 'currencyTo']);

        if ($request->has('code')) {
            $query->where('transaction_code', 'like', '%' . $request->code . '%');
        }

        $transactions = $query->limit(10)->get();

        return response()->json($transactions);
    }

    /**
     * Bulk update transaction status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
            'status' => 'required|in:accept,sent,cancel',
        ]);

        $updated = 0;
        $errors = [];

        foreach ($request->transaction_ids as $id) {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                continue;
            }

            // Validate workflow
            $valid = false;
            if (($request->status == 'accept' || $request->status == 'cancel') && $transaction->status == 'pending') {
                $valid = true;
            } elseif ($request->status == 'sent' && $transaction->status == 'accept') {
                $valid = true;
            }

            if ($valid) {
                $transaction->updateStatus($request->status);
                $updated++;
            } else {
                $errors[] = $transaction->transaction_code;
            }
        }

        $message = "$updated transaction(s) updated successfully.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " transaction(s) skipped due to invalid status.";
        }

        return redirect()->route('transactions.index')->with('success', $message);
    }
}
