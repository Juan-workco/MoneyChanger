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
        return Transaction::where('created_by', $agentId)
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
    public function getMonthlyCommissionReport($month, $agentId = null)
    {
        $startDate = date('Y-m-01', strtotime($month));
        $endDate = date('Y-m-t', strtotime($month));

        $query = Transaction::with('creator')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'sent');

        if ($agentId) {
            $query->where('created_by', $agentId);
        }

        $transactions = $query->get();

        $report = [];

        foreach ($transactions as $transaction) {
            $creatorId = $transaction->created_by;

            // Only count commissions for users who are agents
            if (!$transaction->creator || $transaction->creator->role !== 'agent') {
                continue;
            }

            if (!isset($report[$creatorId])) {
                $report[$creatorId] = (object) [
                    'agent_name' => $transaction->creator->name ?? 'Unknown',
                    'transaction_count' => 0,
                    'total_volume' => 0,
                    'total_commission' => 0,
                    'is_paid' => false,
                ];
            }

            $report[$creatorId]->transaction_count++;
            $report[$creatorId]->total_volume += $transaction->amount_from;
            $report[$creatorId]->total_commission += $transaction->agent_commission;
        }

        return array_values($report);
    }
}
