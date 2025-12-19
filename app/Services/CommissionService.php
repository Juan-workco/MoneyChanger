<?php

namespace App\Services;

use App\ExchangeRate;
use App\Transaction;
use Log;

class CommissionService
{
    /**
     * Calculate profit for a transaction
     *
     * @param float $sellRate
     * @param float $buyRate
     * @param float $amount
     * @return float
     */
    public function calculateProfit($sellRate, $buyRate, $amount)
    {
        Log::info("Profit calculation: sellRate=$sellRate, buyRate=$buyRate, amount=$amount");
        Log::info(($sellRate - $buyRate) * $amount);
        return ($sellRate - $buyRate) * $amount;
    }

    /**
     * Calculate agent commission based on transaction
     *
     * @param Transaction $transaction
     * @param float $commissionPercentage
     * @return float
     */
    public function calculateAgentCommission(Transaction $transaction, $commissionPercentage = 0)
    {
        if ($commissionPercentage <= 0) {
            return 0;
        }

        return $transaction->profit_amount * ($commissionPercentage / 100);
    }

    /**
     * Get total commission for agent in date range
     *
     * @param int $agentId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getAgentCommissionTotal($agentId, $startDate, $endDate)
    {
        return Transaction::where('agent_id', $agentId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'sent')
            ->sum('agent_commission');
    }

    /**
     * Calculate monthly commission for all agents
     *
     * @param string $month (Y-m format)
     * @return array
     */
    public function getMonthlyCommissionReport($month)
    {
        $startDate = date('Y-m-01', strtotime($month));
        $endDate = date('Y-m-t', strtotime($month));

        $transactions = Transaction::with('agent')
            ->whereNotNull('agent_id')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'sent')
            ->get();

        $report = [];

        foreach ($transactions as $transaction) {
            $agentId = $transaction->agent_id;

            if (!isset($report[$agentId])) {
                $report[$agentId] = [
                    'agent' => $transaction->agent,
                    'total_transactions' => 0,
                    'total_profit' => 0,
                    'total_commission' => 0,
                ];
            }

            $report[$agentId]['total_transactions']++;
            $report[$agentId]['total_profit'] += $transaction->profit_amount;
            $report[$agentId]['total_commission'] += $transaction->agent_commission;
        }

        return array_values($report);
    }
}
