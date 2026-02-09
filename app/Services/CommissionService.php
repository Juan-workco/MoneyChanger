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

        // Eager load customer uplines to identify recipients
        $query = Transaction::with(['creator', 'customer.upline1', 'customer.upline2'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'sent');

        // Note: If filtering by agentId, we ideally fetch ALL transactions first 
        // and then filter the RESULTING report, because an agent might be Upline 1 
        // on a transaction created by someone else.
        // However, for performance on huge data, this might be bad.
        // But since commission structure is complex, filtering SQL by "upline1_id = ?" 
        // is hard because upline_id is on customer table.
        // So fetching all and filtering in PHP is the safest correctness path for now.

        $transactions = $query->get();

        $report = [];

        foreach ($transactions as $transaction) {
            // 1. Legacy/Direct Commission to Creator
            if ($transaction->agent_commission > 0 && $transaction->created_by && $transaction->creator) {
                $this->addToReport($report, $transaction->created_by, $transaction->creator->name, $transaction);
            }

            // 2. Upline 1 Commission
            if ($transaction->upline1_commission_amount > 0 && $transaction->customer && $transaction->customer->upline1) {
                $u1 = $transaction->customer->upline1;
                $this->addToReport($report, $u1->id, $u1->name, $transaction, $transaction->upline1_commission_amount);
            }

            // 3. Upline 2 Commission
            if ($transaction->upline2_commission_amount > 0 && $transaction->customer && $transaction->customer->upline2) {
                $u2 = $transaction->customer->upline2;
                $this->addToReport($report, $u2->id, $u2->name, $transaction, $transaction->upline2_commission_amount);
            }
        }

        // Filter by Agent ID if requested
        if ($agentId) {
            if (isset($report[$agentId])) {
                return [$report[$agentId]];
            }
            return [];
        }

        return array_values($report);
    }

    /**
     * Helper to accumulate report data
     */
    private function addToReport(&$report, $userId, $userName, $transaction, $commissionAmount = null)
    {
        if (!isset($report[$userId])) {
            $report[$userId] = (object) [
                'agent_name' => $userName,
                'transaction_count' => 0,
                'total_volume' => 0,
                'total_commission' => 0,
                'is_paid' => false,
            ];
        }

        // If commission amount not specified, use legacy agent_commission
        $amount = $commissionAmount !== null ? $commissionAmount : $transaction->agent_commission;

        $report[$userId]->transaction_count++;
        $report[$userId]->total_volume += $transaction->amount_from;
        $report[$userId]->total_commission += $amount;
    }
}
