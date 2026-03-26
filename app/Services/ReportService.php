<?php

namespace App\Services;

use App\Transaction;
use Carbon\Carbon;
use Log;

class ReportService
{
    /**
     * Generate daily report
     *
     * @param string $date
     * @return array
     */
    public function dailyReport($date, $agentId = null)
    {
        $query = Transaction::whereDate('transaction_date', $date)
            ->with(['customer', 'currencyFrom', 'currencyTo']);

        if ($agentId) {
            $query->where('created_by', $agentId);
        }

        $transactions = $query->get();

        $data = [
            'date' => $date,
            'total_transactions' => $transactions->count(),
            'sent_transactions' => $transactions->where('status', 'sent')->count(),
            'pending_transactions' => $transactions->where('status', 'pending')->count(),
            'total_profit' => $transactions->where('status', 'sent')->sum('profit_amount'),
            'total_commission' => $transactions->where('status', 'sent')->sum('agent_commission'),
            'transactions' => $transactions,
        ];

        return $data;
    }

    /**
     * Calculate opening and closing balance
     *
     * @param string $date
     * @param string $currency
     * @return array
     */
    public function balanceSheet($date, $currency = 'MYR', $agentId = null)
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Opening balance (all sent transactions before this date)
        // Income (In)
        $openingIncomeQuery = Transaction::where('status', 'sent')
            ->where('transaction_date', '<', $startOfDay)
            ->whereHas('currencyTo', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $openingIncomeQuery->where('created_by', $agentId);
        }

        $openingIncome = $openingIncomeQuery->sum('amount_to');

        // Include CashFlow AR (Income)
        $cfOpeningIncomeQuery = \App\CashFlow::where('status', 'completed')
            ->where('type', 'ar')
            ->where('transaction_date', '<', $startOfDay)
            ->whereHas('currency', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $cfOpeningIncomeQuery->where('created_by', $agentId);
        }

        $openingIncome += $cfOpeningIncomeQuery->sum('amount');

        // Expenses (Out)
        $openingExpenseQuery = Transaction::where('status', 'sent')
            ->where('transaction_date', '<', $startOfDay)
            ->whereHas('currencyFrom', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $openingExpenseQuery->where('created_by', $agentId);
        }

        $openingExpense = $openingExpenseQuery->sum('amount_from');

        // Include CashFlow AP (Expense)
        $cfOpeningExpenseQuery = \App\CashFlow::where('status', 'completed')
            ->where('type', 'ap')
            ->where('transaction_date', '<', $startOfDay)
            ->whereHas('currency', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $cfOpeningExpenseQuery->where('created_by', $agentId);
        }

        $openingExpense += $cfOpeningExpenseQuery->sum('amount');

        $openingBalance = $openingIncome - $openingExpense;

        // Today's transactions
        $incomeQuery = Transaction::where('status', 'sent')
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->whereHas('currencyTo', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $incomeQuery->where('created_by', $agentId);
        }

        $todayIncome = $incomeQuery->sum('amount_to');

        // Include CashFlow AR (Income)
        $cfIncomeQuery = \App\CashFlow::where('status', 'completed')
            ->where('type', 'ar')
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->whereHas('currency', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $cfIncomeQuery->where('created_by', $agentId);
        }

        $todayIncome += $cfIncomeQuery->sum('amount');

        $expenseQuery = Transaction::where('status', 'sent')
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->whereHas('currencyFrom', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $expenseQuery->where('created_by', $agentId);
        }

        $todayExpense = $expenseQuery->sum('amount_from');

        // Include CashFlow AP (Expense)
        $cfExpenseQuery = \App\CashFlow::where('status', 'completed')
            ->where('type', 'ap')
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->whereHas('currency', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $cfExpenseQuery->where('created_by', $agentId);
        }

        $todayExpense += $cfExpenseQuery->sum('amount');

        $closingBalance = $openingBalance + $todayIncome - $todayExpense;

        return [
            'date' => $date,
            'currency' => $currency,
            'opening_balance' => $openingBalance,
            'today_income' => $todayIncome,
            'today_expense' => $todayExpense,
            'closing_balance' => $closingBalance,
        ];
    }

    /**
     * Calculate profit and loss for a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function profitLoss($startDate, $endDate, $agentId = null)
    {
        $query = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'sent')
            ->with(['currencyFrom', 'currencyTo']);

        if ($agentId) {
            $query->where('created_by', $agentId);
        }

        $transactions = $query->get();

        $totalProfit = $transactions->sum('profit_amount');
        $totalCommission = $transactions->sum('agent_commission');
        $netProfit = $totalProfit - $totalCommission;

        // Group by currency pair
        $byCurrencyPair = [];

        foreach ($transactions as $transaction) {
            $pair = $transaction->currencyFrom->code . '/' . $transaction->currencyTo->code;

            if (!isset($byCurrencyPair[$pair])) {
                $byCurrencyPair[$pair] = [
                    'pair' => $pair,
                    'count' => 0,
                    'total_profit' => 0,
                ];
            }

            $byCurrencyPair[$pair]['count']++;
            $byCurrencyPair[$pair]['total_profit'] += $transaction->profit_amount;
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_transactions' => $transactions->count(),
            'total_profit' => $totalProfit,
            'total_commission' => $totalCommission,
            'net_profit' => $netProfit,
            'by_currency_pair' => array_values($byCurrencyPair),
        ];
    }

    /**
     * Get monthly summary
     *
     * @param string $month (Y-m format)
     * @return array
     */
    public function monthlySummary($month)
    {
        $startDate = date('Y-m-01', strtotime($month));
        $endDate = date('Y-m-t', strtotime($month));

        return $this->profitLoss($startDate, $endDate);
    }

    /**
     * Generate customer statement — all transactions for a customer in a date range
     *
     * @param int $customerId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function customerStatement($customerId, $startDate, $endDate)
    {
        $customer = \App\Customer::findOrFail($customerId);

        // Sales Orders involving this customer
        $salesOrders = Transaction::where('customer_id', $customerId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['currencyFrom', 'currencyTo'])
            ->get()
            ->map(function ($tx) {
                return [
                    'date' => $tx->transaction_date,
                    'type' => 'Sales Order',
                    'reference' => $tx->order_id ?? $tx->transaction_code,
                    'description' => ($tx->currencyFrom->code ?? '?') . ' → ' . ($tx->currencyTo->code ?? '?'),
                    'debit' => $tx->amount_from,
                    'credit' => $tx->amount_to,
                    'currency_from' => $tx->currencyFrom->code ?? '',
                    'currency_to' => $tx->currencyTo->code ?? '',
                    'status' => $tx->status,
                ];
            });

        // AP/AR/CTC transfers involving this customer
        $transfers = \App\CashFlow::where(function ($q) use ($customerId) {
            $q->where('customer_id', $customerId)
                ->orWhere('related_customer_id', $customerId);
        })
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with('currency')
            ->get()
            ->map(function ($cf) use ($customerId) {
                // If it's AP (Money out to customer) AND customer is the primary customer, Debit.
                // If it's AR (Money in from customer) AND customer is the primary customer, Credit.
                // If it's CTC (Customer to Customer), sender is customer_id (Debit), receiver is related_customer_id (Credit).
                
                $isPrimary = $cf->customer_id == $customerId;
                $debit = 0;
                $credit = 0;
                
                if ($cf->type === 'ap' && $isPrimary) {
                    $debit = $cf->amount; // Our AP is paying the customer (reducing what they owe, or adding to their wallet) -- wait, from Customer's perspective, they receive money? 
                    // Let's reflect actual ledger impact: In LedgerService, AP subtracts from customer balance. AR adds to customer balance.
                    // If AP subtracts, it's a Debit (if balance is normal credit) or vice-versa. 
                    // Let's mimic LedgerService: 
                    // LedgerService: ap -> subtract balance 
                    // LedgerService: ar -> add balance
                    // LedgerService: ctc -> subtract from sender, add to receiver.
                    // Statement view usually shows: Debit (decrease), Credit (increase).
                    $debit = $cf->amount;
                } elseif ($cf->type === 'ar' && $isPrimary) {
                    $credit = $cf->amount;
                } elseif ($cf->type === 'ctc') {
                    if ($isPrimary) {
                        $debit = $cf->amount; // Sender
                    } else {
                        $credit = $cf->amount; // Receiver
                    }
                }

                return [
                    'date' => $cf->transaction_date,
                    'type' => strtoupper($cf->type),
                    'reference' => $cf->cash_flow_code,
                    'description' => strtoupper($cf->type) . ' — ' . ($cf->currency->code ?? '?'),
                    'debit' => $debit,
                    'credit' => $credit,
                    'currency_from' => $cf->currency->code ?? '',
                    'currency_to' => $cf->currency->code ?? '',
                    'status' => $cf->status,
                ];
            });

        // Merge and sort chronologically
        $entries = collect(array_merge($salesOrders->toArray(), $transfers->toArray()))->sortBy('date')->values()->all();

        // Current balances
        $balances = \App\CustomerBalance::where('customer_id', $customerId)
            ->with('currency')
            ->get();

        return [
            'customer' => $customer,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'entries' => $entries,
            'balances' => $balances,
        ];
    }
}
