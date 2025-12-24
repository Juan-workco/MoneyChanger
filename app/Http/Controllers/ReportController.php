<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\CommissionService;
use App\Currency;
use App\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;

class ReportController extends Controller
{
    protected $reportService;
    protected $commissionService;

    public function __construct(ReportService $reportService, CommissionService $commissionService)
    {
        $this->reportService = $reportService;
        $this->commissionService = $commissionService;
    }

    /**
     * Show daily report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $agentId = auth()->user()->isAgent() ? auth()->id() : null;
        $report = $this->reportService->dailyReport($date, $agentId);

        // Structure data to match view expectations
        $transactions = $report['transactions'] ?? [];
        $summary = [
            'total_count' => $report['total_transactions'] ?? 0,
            'total_volume' => $report['total_profit'] ?? 0,
            'total_profit' => $report['total_profit'] ?? 0,
        ];

        return view('reports.daily', compact('transactions', 'summary', 'date'));
    }

    /**
     * Show balance sheet
     */
    public function balanceSheet(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $agentId = auth()->user()->isAgent() ? auth()->id() : null;

        $currencies = Currency::active()->get();
        $balances = [];
        $detailedBalances = [];

        foreach ($currencies as $curr) {
            $sheet = $this->reportService->balanceSheet($date, $curr->code, $agentId);

            $balances[$curr->code] = $sheet['closing_balance'];

            $detailedBalances[] = [
                'currency' => $curr->code,
                'opening' => $sheet['opening_balance'],
                'in' => $sheet['today_income'],
                'out' => $sheet['today_expense'],
                'closing' => $sheet['closing_balance']
            ];
        }

        return view('reports.balance_sheet', compact('balances', 'detailedBalances', 'date', 'currencies'));
    }

    /**
     * Show profit and loss report
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $agentId = auth()->user()->isAgent() ? auth()->id() : null;

        $report = $this->reportService->profitLoss($startDate, $endDate, $agentId);

        $totalProfit = $report['total_profit'];
        $totalTransactions = $report['total_transactions'];
        $profitByPair = collect($report['by_currency_pair'])->map(function ($item) {
            return [
                'name' => $item['pair'],
                'count' => $item['count'],
                'volume' => 0, // Volume not calculated by service but needed by view
                'profit' => $item['total_profit']
            ];
        });

        return view('reports.profit_loss', compact('report', 'startDate', 'endDate', 'totalProfit', 'totalTransactions', 'profitByPair'));
    }

    /**
     * Show commission report
     */
    public function commissionReport(Request $request)
    {
        $month = $request->get('month', date('Y-m'));
        $agentId = $request->get('agent_id');

        // If agent is logged in, they can only see their own commission
        if (auth()->user()->isAgent()) {
            $agentId = auth()->id();
        }

        $commissions = $this->commissionService->getMonthlyCommissionReport($month, $agentId);
        $agents = User::where('role', 'agent')->get();

        return view('reports.commission', compact('commissions', 'month', 'agents'));
    }

    /**
     * Export daily report as PDF
     */
    public function exportPDF(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $agentId = auth()->user()->isAgent() ? auth()->id() : null;
        $report = $this->reportService->dailyReport($date, $agentId);

        $pdf = PDF::loadView('reports.pdf.daily', compact('report', 'date'));
        return $pdf->download('daily-report-' . $date . '.pdf');
    }

    /**
     * Export profit/loss report as PDF
     */
    public function exportProfitLossPDF(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $agentId = auth()->user()->isAgent() ? auth()->id() : null;
        $report = $this->reportService->profitLoss($startDate, $endDate, $agentId);

        $pdf = PDF::loadView('reports.pdf.profit_loss', compact('report', 'startDate', 'endDate'));
        return $pdf->download('profit-loss-' . $startDate . '-to-' . $endDate . '.pdf');
    }
}
