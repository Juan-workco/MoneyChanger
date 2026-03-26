<?php

namespace App\Http\Controllers;

use App\CashFlow;
use App\Customer;
use App\Currency;
use App\CustomerBalance;
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

        // Role-based filtering: agents only see their own, admins see all
        $user = Auth::user();
        if ($user->role !== 'admin') {
            $query->where('created_by', $user->id);
        }

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

        // Check if Day is Closed
        if (\App\DayClose::where('close_date', $transactionDate->format('Y-m-d'))->where('status', 'closed')->exists()) {
            return back()->with('error', "Cannot create cash flow: The accounting date " . $transactionDate->format('Y-m-d') . " is already closed.")->withInput();
        }

        // Check Credit Limits
        $adjustment1 = 0;
        $adjustment2 = 0;
        if ($request->type === 'ap')
            $adjustment1 = -$request->amount;
        elseif ($request->type === 'ar')
            $adjustment1 = $request->amount;
        elseif ($request->type === 'ctc') {
            $adjustment1 = -$request->amount;
            $adjustment2 = $request->amount;
        }

        if ($adjustment1 != 0) {
            $check = \App\Services\LedgerService::checkCreditLimit($request->customer_id, $request->currency_id, $adjustment1);
            if ($check && $check['exceeded']) {
                $msg = "Customer exceeds credit limit wrapper of {$check['limit']}. New balance would be " . abs($check['new']);
                if ($check['enforcement'] === 'block')
                    return back()->with('error', "BLOCKED: " . $msg)->withInput();
                elseif (!$request->has('force_credit_limit'))
                    return back()->withInput()->with('credit_warning', "⚠️ " . $msg . " Check 'Confirm Override' below.");
            }
        }
        if ($adjustment2 != 0 && $request->related_customer_id) {
            $check = \App\Services\LedgerService::checkCreditLimit($request->related_customer_id, $request->currency_id, $adjustment2);
            if ($check && $check['exceeded']) {
                $msg = "Related customer exceeds credit limit of {$check['limit']}.";
                if ($check['enforcement'] === 'block')
                    return back()->with('error', "BLOCKED: " . $msg)->withInput();
                elseif (!$request->has('force_credit_limit'))
                    return back()->withInput()->with('credit_warning', "⚠️ " . $msg . " Check 'Confirm Override' below.");
            }
        }

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

        // Adjust balances using LedgerService
        if ($request->type === 'ap') {
            // AP: We pay customer → deduct from their balance and our wallet
            \App\Services\LedgerService::subtractBalance($transactionDate, 'ap', $cashFlow->id, 'customer', $request->customer_id, $request->currency_id, $request->amount, Auth::id());
            if ($request->from_account_id) {
                \App\Services\LedgerService::subtractBalance($transactionDate, 'ap', $cashFlow->id, 'account', $request->from_account_id, $request->currency_id, $request->amount, Auth::id());
            }
        } elseif ($request->type === 'ar') {
            // AR: Customer pays us → add to their balance and our wallet
            \App\Services\LedgerService::addBalance($transactionDate, 'ar', $cashFlow->id, 'customer', $request->customer_id, $request->currency_id, $request->amount, Auth::id());
            if ($request->to_account_id) {
                \App\Services\LedgerService::addBalance($transactionDate, 'ar', $cashFlow->id, 'account', $request->to_account_id, $request->currency_id, $request->amount, Auth::id());
            }
        } elseif ($request->type === 'ctc') {
            // CTC: Transfer from sender to receiver
            \App\Services\LedgerService::subtractBalance($transactionDate, 'ctc', $cashFlow->id, 'customer', $request->customer_id, $request->currency_id, $request->amount, Auth::id());
            \App\Services\LedgerService::addBalance($transactionDate, 'ctc', $cashFlow->id, 'customer', $request->related_customer_id, $request->currency_id, $request->amount, Auth::id());
        }

        ActivityLogService::log('cash_flow_created', "Created {$request->type} entry {$code}", $cashFlow);

        return redirect()->route('cash-flows.index')
            ->with('success', "Cash Flow {$code} created successfully.");
    }

    /**
     * Display the specified cash flow.
     */
    public function show($id)
    {
        $cashFlow = CashFlow::with(['customer', 'relatedCustomer', 'currency', 'creator', 'fromAccount', 'toAccount'])->findOrFail($id);

        // Role-based access: agents can only view their own cash flows
        $user = Auth::user();
        if ($user->role !== 'admin' && $cashFlow->created_by !== $user->id) {
            abort(403, 'You are not authorized to view this cash flow.');
        }

        return view('cash-flows.show', compact('cashFlow'));
    }

    /**
     * Get Customer Balance (AJAX)
     * Reads from the customer_balances table.
     */
    public function getBalance(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $balance = \App\Services\LedgerService::getBalance('customer', $request->customer_id, $request->currency_id);

        return response()->json([
            'balance' => $balance,
        ]);
    }
}
