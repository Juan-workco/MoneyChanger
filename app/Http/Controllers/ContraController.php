<?php

namespace App\Http\Controllers;

use App\Contra;
use App\Customer;
use App\Currency;
use App\Services\LedgerService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContraController extends Controller
{
    /**
     * Display a listing of contras.
     */
    public function index()
    {
        // Assuming permissions similar to cash flows or transactions
        $contras = Contra::with(['customer', 'currencyA', 'currencyB', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('contras.index', compact('contras'));
    }

    /**
     * Show the form for creating a new contra.
     */
    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $currencies = Currency::active()->orderBy('code')->get();

        return view('contras.create', compact('customers', 'currencies'));
    }

    /**
     * Store a newly created contra in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'currency_a_id' => 'required|exists:currencies,id',
            'amount_a' => 'required|numeric|min:0.01',
            'currency_b_id' => 'required|exists:currencies,id|different:currency_a_id',
            'amount_b' => 'required|numeric|min:0.01',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date'
        ]);

        $transactionDate = Carbon::parse($request->transaction_date);

        // Check if Day is Closed
        if (\App\DayClose::where('close_date', $transactionDate->format('Y-m-d'))->where('status', 'closed')->exists()) {
            return back()->with('error', "Cannot create contra: The accounting date " . $transactionDate->format('Y-m-d') . " is already closed.")->withInput();
        }

        DB::beginTransaction();

        try {
            // Generate Contra Code
            $count = Contra::whereDate('created_at', Carbon::today())->count() + 1;
            $code = date('ymd') . '-CNT' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $contra = Contra::create([
                'contra_code' => $code, // Added this to migration? Wait, let's check migration/model. Model has contra_code
                'customer_id' => $request->customer_id,
                'currency_a_id' => $request->currency_a_id,
                'amount_a' => $request->amount_a,
                'currency_b_id' => $request->currency_b_id,
                'amount_b' => $request->amount_b,
                'exchange_rate' => $request->exchange_rate,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Adjust balances
            // Contra reduces balances. E.g., customer owes us Currency A (+ balance), we owe customer Currency B (- balance).
            // Netting off means we reduce customer debt in Currency A (Credit/Subtract)
            // And we reduce our debt in Currency B (Debit/Add)
            // But from generic view, user enters amounts to adjust. 
            // In typical contra: you are offsetting two balances.
            // So we subtract from Currency A and add to Currency B? Or user specifies?
            // Usually Contra implies: customer balance in Currency A is offset by Customer Balance in Currency B.
            // Let's assume Amount A is the amount paying off Currency A debt, Amount B pays off Currency B credit.
            // So we subtract Amount A from Currency A balance, and Add Amount B to Currency B balance.

            LedgerService::subtractBalance(
                $transactionDate,
                'contra',
                $contra->id,
                'customer',
                $request->customer_id,
                $request->currency_a_id,
                $request->amount_a,
                Auth::id()
            );

            LedgerService::addBalance(
                $transactionDate,
                'contra',
                $contra->id,
                'customer',
                $request->customer_id,
                $request->currency_b_id,
                $request->amount_b,
                Auth::id()
            );

            ActivityLogService::log('contra_created', "Created contra {$code}", $contra);

            DB::commit();

            return redirect()->route('contras.index')->with('success', "Contra created successfully ($code).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Error creating contra: " . $e->getMessage())->withInput();
        }
    }
}
