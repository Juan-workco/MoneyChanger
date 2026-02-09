<?php

namespace App\Http\Controllers;

use App\CashFlow;
use App\Customer;
use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;

class CashFlowController extends Controller
{
    /**
     * Display a listing of cash flows.
     */
    public function index(Request $request)
    {
        $query = CashFlow::with(['customer', 'relatedCustomer', 'currency', 'creator']);

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id)
                    ->orWhere('related_customer_id', $request->customer_id);
            });
        }

        if ($request->has('search') && $request->search) {
            $query->where('cash_flow_code', 'like', '%' . $request->search . '%');
        }

        // Date range filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $cashFlows = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('cash-flows.index', compact('cashFlows'));
    }

    /**
     * Show the form for creating a new cash flow.
     */
    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $currencies = Currency::active()->orderBy('code')->get();
        $receivingAccounts = \App\ReceivingAccount::where('is_active', true)->orderBy('account_name')->get();

        return view('cash-flows.create', compact('customers', 'currencies', 'receivingAccounts'));
    }

    /**
     * Store a newly created cash flow.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:ap,ar,ctc',
            'customer_id' => 'required|exists:customers,id',
            'related_customer_id' => 'required_if:type,ctc|nullable|exists:customers,id|different:customer_id',
            'from_account_id' => 'required_if:type,ap|nullable|exists:receiving_accounts,id',
            'to_account_id' => 'required_if:type,ar|nullable|exists:receiving_accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $transactionDate = \Carbon\Carbon::parse($request->transaction_date);
        $isBackdated = $transactionDate->startOfDay()->lt(\Carbon\Carbon::today());

        $code = CashFlow::generateCode($request->type);

        // Generate automatic note for backdated entries
        $notes = $request->notes;
        if ($isBackdated) {
            $backdatedNote = "BACKDATED ENTRY: Transaction date " . $transactionDate->format('Y-m-d') .
                " (Created on " . \Carbon\Carbon::now()->format('Y-m-d H:i:s') .
                " by " . Auth::user()->username . ")";
            $notes = $notes ? $backdatedNote . "\n\n" . $notes : $backdatedNote;
        }

        $cashFlow = CashFlow::create([
            'cash_flow_code' => $code,
            'type' => $request->type,
            'customer_id' => $request->customer_id,
            'related_customer_id' => $request->related_customer_id,
            'from_account_id' => $request->from_account_id,
            'to_account_id' => $request->to_account_id,
            'amount' => $request->amount,
            'currency_id' => $request->currency_id,
            'transaction_date' => $transactionDate,
            'is_backdated' => $isBackdated,
            'notes' => $notes,
            'status' => 'pending', // Default status
            'created_by' => Auth::id(),
        ]);

        ActivityLogService::log('cash_flow_created', "Created {$request->type} entry {$code}", $cashFlow);

        return redirect()->route('dashboard')
            ->with('success', "Cash Flow {$code} created successfully.");
    }

    /**
     * Display the specified cash flow.
     */
    public function show($id)
    {
        $cashFlow = CashFlow::with(['customer', 'relatedCustomer', 'currency', 'creator', 'fromAccount', 'toAccount'])->findOrFail($id);
        return view('cash-flows.show', compact('cashFlow'));
    }

    /**
     * Get Customer Balance (AJAX)
     * Placeholder implementation - sums up cash flows for now.
     */
    public function getBalance(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $customerId = $request->customer_id;
        $currencyId = $request->currency_id;

        // Calculate Balance: AR (Money In) - AP (Money Out)
        // Positive Balance = Customer Owes Us? OR We Owe Customer?
        // Usually, in accounting: 
        // AR = Debit (Asset, Customer owes us)
        // AP = Credit (Liability, We owe customer)
        // Let's define: Balance > 0 means Customer Owes Us.

        $ar = CashFlow::where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->where('type', 'ar')
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        $ap = CashFlow::where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->where('type', 'ap')
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        // CTC: 
        // Source (From) -> Increases Debt (They pay us to pay someone else? No.)
        // CTC is "Customer A to Customer B".
        // A sends money to B.
        // If A sends money, A's balance with us...
        // If we act as intermediary:
        // A gives us money (AR from A) -> We give money to B (AP to B).
        // A single CTC record implies: A's balance decreases (They spent money), B's increases (They received money).
        // If "Balance" = "Amount Customer Owes Us" (Debit Balance)
        // A Transfer to B:
        // A's Debt Increases? No, A is paying. 
        // Ideally: A has Credit with us. A Uses Credit to Pay B. A's Credit Decreases (Debt Increases).
        // B Receives Credit. B's Credit Increases (Debt Decreases).

        $ctcOut = CashFlow::where('customer_id', $customerId) // Sender
            ->where('currency_id', $currencyId)
            ->where('type', 'ctc')
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        $ctcIn = CashFlow::where('related_customer_id', $customerId) // Receiver
            ->where('currency_id', $currencyId)
            ->where('type', 'ctc')
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        // Net Balance (from Customer Perspective relative to Us)
        // Let's assume a simplified "Wallet" view.
        // Wallet Balance = (Money In) - (Money Out)
        // Money In = AP (We pay them/Deposit) + CTC In
        // Money Out = AR (They Withdraw/Pay Us) + CTC Out
        // Wait, AP/AR names are tricky.
        // AP = We Pay Customer. (Customer Balance Increases)
        // AR = Customer Pays Us. (Customer Balance Decreases)

        // Let's stick to "Owe Us" (Debt)
        // Owe Us = (Services/FX Sales) - (Payments/AR) + (Withdrawals/AP)
        // This is getting complex without FX.

        // Let's just return the sums for now and let the UI interpret.

        $balance = $ap - $ar + $ctcIn - $ctcOut;

        // This is extremely rough.

        return response()->json([
            'balance' => $balance,
            'details' => [
                'ar' => $ar,
                'ap' => $ap,
                'ctc_out' => $ctcOut,
                'ctc_in' => $ctcIn
            ]
        ]);
    }
}
