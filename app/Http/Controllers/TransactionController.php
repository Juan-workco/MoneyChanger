<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Customer;
use App\Currency;
use App\ExchangeRate;
use App\User;
use App\CustomerBalance;
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
        $query = Transaction::with(['customer', 'currencyFrom', 'currencyTo', 'creator']);

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

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        log::debug($transactions);

        $customers = Customer::active()
            ->when(Auth::user()->isAgent(), function ($query) {
                $query->where('agent_id', Auth::user()->id);
            })
            ->orderBy('name')
            ->get();

        return view('transactions.index', compact('transactions', 'customers'));
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create()
    {
        $customers = Customer::active()
            ->with(['commissions', 'upline1', 'upline2'])
            ->when(Auth::user()->isAgent(), function ($query) {
                $query->where('agent_id', Auth::user()->id);
            })
            ->orderBy('name')
            ->get();

        // Get all active currencies for both From and To dropdowns
        $currencies = Currency::active()->orderBy('code')->get();

        // Get all currency pairs for default points
        $currencyPairs = \App\CurrencyPair::where('is_active', true)->get();

        // Get all active exchange rates for JS filtering
        $exchangeRates = ExchangeRate::where('is_active', true)
            ->select('id', 'currency_from_id', 'currency_to_id', 'buy_rate', 'sell_rate')
            ->get();

        return view('transactions.create', compact('customers', 'currencies', 'currencyPairs', 'exchangeRates'));
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
            'upline1_commission_amount' => 'nullable|numeric',
            'upline2_commission_amount' => 'nullable|numeric',
            'upline1_point' => 'nullable|numeric',
            'upline2_point' => 'nullable|numeric',
            'currency_pair_id' => 'nullable|exists:currency_pairs,id',
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

            // Determine effective rates
            $buyRate = $exchangeRate->buy_rate;
            // Use manual rate if provided, otherwise system rate
            $sellRate = $request->filled('sell_rate') ? $request->input('sell_rate') : $exchangeRate->sell_rate;

            $amountTo = $request->amount_from * $sellRate;

            // Check for Backdating
            $transactionDate = \Carbon\Carbon::parse($request->transaction_date);
            $isBackdated = $transactionDate->startOfDay()->lt(\Carbon\Carbon::today());

            // Check if Day is Closed
            if (\App\DayClose::where('close_date', $transactionDate->format('Y-m-d'))->where('status', 'closed')->exists()) {
                DB::rollBack();
                return back()->with('error', "Cannot create transaction: The accounting date " . $transactionDate->format('Y-m-d') . " is already closed.")->withInput();
            }

            // Duplicate Detection Check
            $duplicate = \App\Transaction::where('customer_id', $request->customer_id)
                ->where('currency_from_id', $request->currency_from_id)
                ->where('currency_to_id', $request->currency_to_id)
                ->whereBetween('amount_from', [$request->amount_from * 0.99, $request->amount_from * 1.01])
                ->where('created_at', '>=', now()->subMinutes(10))
                ->first();

            if ($duplicate && !$request->has('force_duplicate')) {
                DB::rollBack();
                return back()->withInput()->with('duplicate_warning', "⚠️ Possible duplicate: Order {$duplicate->transaction_code} (" . $duplicate->created_at->diffForHumans() . "). Check 'Confirm Duplicate' below if you are sure.");
            }

            // Credit Limit Check
            $limitCheckFrom = \App\Services\LedgerService::checkCreditLimit($request->customer_id, $request->currency_from_id, -$request->amount_from);
            if ($limitCheckFrom && $limitCheckFrom['exceeded']) {
                $msg = "Customer exceeds credit limit of {$limitCheckFrom['limit']}. (New balance would be " . abs($limitCheckFrom['new']) . ")";
                if ($limitCheckFrom['enforcement'] === 'block') {
                    DB::rollBack();
                    return back()->with('error', "BLOCKED: " . $msg)->withInput();
                } else if (!$request->has('force_credit_limit')) {
                    DB::rollBack();
                    return back()->withInput()->with('credit_warning', "⚠️ WARNING: " . $msg . " Check 'Confirm Credit Limit Override'.");
                }
            }

            $limitCheckTo = \App\Services\LedgerService::checkCreditLimit($request->customer_id, $request->currency_to_id, $amountTo);
            if ($limitCheckTo && $limitCheckTo['exceeded']) {
                $msg = "Customer exceeds credit limit of {$limitCheckTo['limit']}. (New balance would be " . abs($limitCheckTo['new']) . ")";
                if ($limitCheckTo['enforcement'] === 'block') {
                    DB::rollBack();
                    return back()->with('error', "BLOCKED: " . $msg)->withInput();
                } else if (!$request->has('force_credit_limit')) {
                    DB::rollBack();
                    return back()->withInput()->with('credit_warning', "⚠️ WARNING: " . $msg . " Check 'Confirm Credit Limit Override'.");
                }
            }

            // Monthly Rate & Profit Calculation
            $monthlyRate = null;
            $monthlyRateVal = null;
            $profit = 0;

            if ($request->currency_pair_id) {
                $month = $transactionDate->format('Y-m');
                $monthlyRate = \App\MonthlyRate::where('currency_pair_id', $request->currency_pair_id)
                    ->where('month', $month)
                    ->first();

                if ($monthlyRate) {
                    $monthlyRateVal = $monthlyRate->rate;
                    // Profit = (Sales Rate - Monthly Fixed Rate) * Amount
                    // Assuming Sales Rate > Monthly Rate = Profit
                    $profit = ($sellRate - $monthlyRateVal) * $request->amount_from;
                } else {
                    // Fallback to standard profit (Sell - Buy)
                    // Or keep 0? User said "difference between sales input and fixed monthly... recorded as FX profit".
                    // If no monthly, maybe use old logic:
                    $profit = ($sellRate - $buyRate) * $request->amount_from;
                }
            } else {
                $profit = ($sellRate - $buyRate) * $request->amount_from; // Fallback
            }

            $currentNotes = $validated['notes'] ?? '';
            if ($isBackdated) {
                $currentNotes .= "\n[System: Backdated Transaction]";
            }
            if ($request->has('force_duplicate')) {
                $currentNotes .= "\n[System: Created despite duplicate warning]";
            }
            if ($request->has('force_credit_limit')) {
                $currentNotes .= "\n[System: Created despite credit limit warning]";
            }
            if ($monthlyRateVal) {
                $currentNotes .= "\n[System: FX Profit using Monthly Rate: $monthlyRateVal]";
            }

            $transactionData = array_merge($validated, [
                'exchange_rate_id' => $exchangeRate->id,
                'amount_to' => $amountTo,
                'buy_rate' => $buyRate,
                'sell_rate' => $sellRate,
                'profit_amount' => $profit,
                'agent_commission' => 0,
                'status' => 'pending',
                'created_by' => Auth::id(),
                'currency_pair_id' => $request->currency_pair_id,
                'upline1_commission_amount' => $request->upline1_commission_amount ?? 0,
                'upline1_point' => $request->upline1_point,
                'upline2_commission_amount' => $request->upline2_commission_amount ?? 0,
                'upline2_point' => $request->upline2_point,
                'is_backdated' => $isBackdated,
                'monthly_rate' => $monthlyRateVal,
                'service_fee' => $request->service_fee ?? 0,
                'notes' => trim($currentNotes)
            ]);

            $transaction = Transaction::create($transactionData);

            // Adjust balances using LedgerService
            // Deduct from From Currency balance (subtractBalance = Credit)
            \App\Services\LedgerService::subtractBalance(
                $transactionDate,
                'sales_order',
                $transaction->id,
                'customer',
                $request->customer_id,
                $request->currency_from_id,
                $request->amount_from,
                Auth::id()
            );

            // Add to To Currency balance (addBalance = Debit)
            \App\Services\LedgerService::addBalance(
                $transactionDate,
                'sales_order',
                $transaction->id,
                'customer',
                $request->customer_id,
                $request->currency_to_id,
                $amountTo,
                Auth::id()
            );

            // Log activity
            ActivityLogService::log('transaction_created', "Created transaction {$transaction->transaction_code} for customer " . $transaction->customer->name, $transaction);

            // Dispatch Notifications for overrides
            if ($request->has('force_duplicate') && $duplicate) {
                $admins = \App\User::whereIn('role', ['admin', 'super-admin'])->get();
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DuplicateTransactionWarning($transaction, $duplicate));
            }

            if ($request->has('force_credit_limit')) {
                $admins = \App\User::whereIn('role', ['admin', 'super-admin'])->get();

                // Let's pass the first breached limit for simplicity
                $breachedLimit = ($limitCheckFrom && $limitCheckFrom['exceeded']) ? $limitCheckFrom :
                    (($limitCheckTo && $limitCheckTo['exceeded']) ? $limitCheckTo : ['limit' => 'Unknown']);

                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\CreditLimitBreached($transaction, $transaction->customer, $breachedLimit));
            }

            DB::commit();

            // Send Telegram notification
            TelegramController::notifyOrderEvent($transaction, 'created');

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

        // Check permission for agents
        if (Auth::user()->isAgent() && $transaction->created_by !== Auth::id()) {
            return redirect()->route('transactions.index')->with('error', 'You are not authorized to edit this transaction.');
        }

        $customers = Customer::active()
            ->with(['commissions', 'upline1', 'upline2'])
            ->when(Auth::user()->isAgent(), function ($query) {
                $query->where('agent_id', Auth::user()->id);
            })
            ->orderBy('name')
            ->get();

        $currencies = Currency::active()->orderBy('code')->get();
        // Get all currency pairs for default points
        $currencyPairs = \App\CurrencyPair::where('is_active', true)->get();

        return view('transactions.edit', compact('transaction', 'customers', 'currencies', 'currencyPairs'));
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Check permission for agents
        if (Auth::user()->isAgent() && $transaction->created_by !== Auth::id()) {
            return redirect()->route('transactions.index')->with('error', 'You are not authorized to update this transaction.');
        }

        $originalDate = \Carbon\Carbon::parse($transaction->transaction_date);
        $newDate = \Carbon\Carbon::parse($request->transaction_date);

        if (
            \App\DayClose::where('close_date', $originalDate->format('Y-m-d'))->where('status', 'closed')->exists() ||
            \App\DayClose::where('close_date', $newDate->format('Y-m-d'))->where('status', 'closed')->exists()
        ) {

            $lockedFieldsChanged = (
                $request->amount_from != $transaction->amount_from ||
                $request->currency_from_id != $transaction->currency_from_id ||
                $request->currency_to_id != $transaction->currency_to_id ||
                $request->customer_id != $transaction->customer_id ||
                $originalDate->format('Y-m-d') !== $newDate->format('Y-m-d')
            );

            if ($lockedFieldsChanged) {
                return back()->with('error', "This date is closed. You can only update the status and notes.")->withInput();
            }
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount_from' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'upline1_commission_amount' => 'nullable|numeric',
            'upline2_commission_amount' => 'nullable|numeric',
            'upline1_point' => 'nullable|numeric',
            'upline2_point' => 'nullable|numeric',
            'currency_pair_id' => 'nullable|exists:currency_pairs,id',
        ]);

        // Recalculate amounts if amount changed
        if ($request->amount_from != $transaction->amount_from) {
            $validated['amount_to'] = $request->amount_from * $transaction->sell_rate;
            // Use existing simple calculation logic consistent with store method for now to avoid dependency issues if service isn't fully ready
            // Or if previous code used it, we can try to use it. The view_file showed it was there.
            // But to be safe and quick, I'll use the simple formula as I did in store:
            $validated['profit_amount'] = ($transaction->sell_rate - $transaction->buy_rate) * $validated['amount_from'];
        }

        $transaction->update(array_merge($validated, [
            'currency_pair_id' => $request->currency_pair_id, // Allow updating currency pair linkage if needed
            'upline1_commission_amount' => $request->upline1_commission_amount,
            'upline1_point' => $request->upline1_point,
            'upline2_commission_amount' => $request->upline2_commission_amount,
            'upline2_point' => $request->upline2_point,
        ]));

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

        // Check permission for agents
        if (Auth::user()->isAgent() && $transaction->created_by !== Auth::id()) {
            return redirect()->route('transactions.index')->with('error', 'You are not authorized to delete this transaction.');
        }

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

        // Send Telegram notification
        TelegramController::notifyOrderEvent($transaction, 'status_changed');

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

        if (Auth::user()->isAgent()) {
            $query->where('created_by', Auth::id());
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

        $transactions = Transaction::whereIn('id', $request->transaction_ids);

        // Agent can only update their own
        if (Auth::user()->isAgent()) {
            $transactions->where('created_by', Auth::id());
        }

        $transactions = $transactions->get();

        DB::beginTransaction();
        try {
            foreach ($transactions as $transaction) {
                $transaction->updateStatus($request->status);
            }
            DB::commit();

            return redirect()->back()
                ->with('success', count($transactions) . ' transaction(s) status updated to ' . ucfirst($request->status));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating transactions: ' . $e->getMessage());
        }
    }

    /**
     * Get available To currencies based on selected From currency (AJAX)
     */
    public function getAvailableToCurrencies(Request $request)
    {
        $fromCurrencyId = $request->input('from_currency_id');

        if (!$fromCurrencyId) {
            return response()->json(['currencies' => []]);
        }

        // Find currencies that have an active exchange rate where currency_from_id matches
        $toCurrencyIds = ExchangeRate::where('is_active', true)
            ->where('currency_from_id', $fromCurrencyId)
            ->pluck('currency_to_id')
            ->unique();

        $currencies = Currency::whereIn('id', $toCurrencyIds)
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'symbol']);

        return response()->json(['currencies' => $currencies]);
    }
}
