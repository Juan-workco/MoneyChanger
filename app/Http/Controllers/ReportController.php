<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\CommissionService;
use App\Currency;
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
        $report = $this->reportService->dailyReport($date);

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
        $currency = $request->get('currency', 'MYR');

        $balance = $this->reportService->balanceSheet($date, $currency);
        $currencies = Currency::active()->get();

        return view('reports.balance_sheet', compact('balance', 'date', 'currency', 'currencies'));
    }

    /**
     * Show profit and loss report
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $report = $this->reportService->profitLoss($startDate, $endDate);

        return view('reports.profit_loss', compact('report', 'startDate', 'endDate'));
    }

    /**
     * Show commission report
     */
    public function commissionReport(Request $request)
    {
        $month = $request->get('month', date('Y-m'));
        $report = $this->commissionService->getMonthlyCommissionReport($month);

        return view('reports.commission', compact('report', 'month'));
    }

    /**
     * Export daily report as PDF
     */
    public function exportPDF(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $report = $this->reportService->dailyReport($date);

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
        $report = $this->reportService->profitLoss($startDate, $endDate);

        $pdf = PDF::loadView('reports.pdf.profit_loss', compact('report', 'startDate', 'endDate'));
        return $pdf->download('profit-loss-' . $startDate . '-to-' . $endDate . '.pdf');
    }
}
