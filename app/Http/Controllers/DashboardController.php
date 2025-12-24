<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Customer;
use App\Currency;
use App\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Show the dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $isAgent = $user->isAgent();

        // Summary statistics
        $stats = [
            'total_transactions' => $isAgent ? Transaction::where('created_by', $user->id)->count() : Transaction::count(),
            'today_transactions' => $isAgent
                ? Transaction::where('created_by', $user->id)->whereDate('transaction_date', today())->count()
                : Transaction::whereDate('transaction_date', today())->count(),
            'pending_transactions' => $isAgent
                ? Transaction::where('created_by', $user->id)->where('status', 'pending')->count()
                : Transaction::where('status', 'pending')->count(),
            'total_customers' => $isAgent ? Customer::where('agent_id', $user->id)->count() : Customer::count(),
            'active_currencies' => Currency::where('is_active', true)->count(),
            'active_exchange_rates' => ExchangeRate::where('is_active', true)->count(),
        ];

        // Today's profit
        $todayProfit = Transaction::whereDate('transaction_date', today())
            ->where('status', 'sent')
            ->when($isAgent, function ($q) use ($user) {
                return $q->where('created_by', $user->id);
            })
            ->sum('profit_amount');

        // This month's profit
        $monthProfit = Transaction::whereYear('transaction_date', date('Y'))
            ->whereMonth('transaction_date', date('m'))
            ->where('status', 'sent')
            ->when($isAgent, function ($q) use ($user) {
                return $q->where('created_by', $user->id);
            })
            ->sum('profit_amount');

        // Recent transactions
        $recentTransactions = Transaction::with(['customer', 'currencyFrom', 'currencyTo'])
            ->when($isAgent, function ($q) use ($user) {
                return $q->where('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transaction status breakdown
        $transactionsByStatus = Transaction::select('status', DB::raw('count(*) as count'))
            ->when($isAgent, function ($q) use ($user) {
                return $q->where('created_by', $user->id);
            })
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $activeRates = ExchangeRate::with(['currencyFrom', 'currencyTo'])
            ->where('is_active', true)
            ->orderBy('effective_date', 'desc')
            ->get();

        return view('dashboard', compact(
            'stats',
            'todayProfit',
            'monthProfit',
            'recentTransactions',
            'transactionsByStatus',
            'activeRates'
        ));
    }
}
