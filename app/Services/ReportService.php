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

        Log::debug($data);

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

        $expenseQuery = Transaction::where('status', 'sent')
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->whereHas('currencyFrom', function ($q) use ($currency) {
                $q->where('code', $currency);
            });

        if ($agentId) {
            $expenseQuery->where('created_by', $agentId);
        }

        $todayExpense = $expenseQuery->sum('amount_from');

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
}
