<?php

namespace App\Services;

use App\LedgerEntry;
use App\CustomerBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LedgerService
{
    /**
     * Record a movement in the ledger.
     */
    public static function recordEntry(
        $transactionDate,
        $referenceType,
        $referenceId,
        $accountType,
        $accountId,
        $currencyId,
        $debit = 0,
        $credit = 0,
        $userId = null
    ) {
        if ($debit == 0 && $credit == 0) {
            return null;
        }

        $userId = $userId ?? Auth::id();

        return DB::transaction(function () use ($transactionDate, $referenceType, $referenceId, $accountType, $accountId, $currencyId, $debit, $credit, $userId) {
            $latestEntry = LedgerEntry::where('account_type', $accountType)
                ->where('account_id', $accountId)
                ->where('currency_id', $currencyId)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $currentBalance = $latestEntry ? $latestEntry->running_balance : 0;

            // Debit increases Asset/Expense. Credit decreases Asset/Expense.
            // Customers Owe Us = Asset (+)
            // Wallets Balance = Asset (+)
            $newBalance = $currentBalance + $debit - $credit;

            $entry = LedgerEntry::create([
                'transaction_date' => $transactionDate,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'account_type' => $accountType,
                'account_id' => $accountId,
                'currency_id' => $currencyId,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $newBalance,
                'created_by' => $userId,
            ]);

            // Sync with legacy CustomerBalance to not break existing frontend assumptions immediately
            if ($accountType === 'customer') {
                CustomerBalance::updateOrCreate(
                    ['customer_id' => $accountId, 'currency_id' => $currencyId],
                    ['balance' => $newBalance]
                );
            }

            // Balance Alert: Check if wallet/bank account dropped below threshold
            if ($accountType === 'account') {
                $account = \App\ReceivingAccount::find($accountId);
                if ($account && $account->alert_threshold !== null && $newBalance < $account->alert_threshold) {
                    $admins = \App\User::whereIn('role', ['admin', 'super-admin'])->get();
                    $currencyCode = \App\Currency::find($currencyId)->code ?? 'N/A';
                    foreach ($admins as $admin) {
                        // In-app notification
                        \Illuminate\Support\Facades\Notification::send(
                            $admin,
                            new \App\Notifications\BalanceAlert($account, $currencyCode, $newBalance)
                        );
                    }
                }
            }

            return $entry;
        });
    }

    /**
     * Helper to increase balance (Debit)
     */
    public static function addBalance($transactionDate, $referenceType, $referenceId, $accountType, $accountId, $currencyId, $amount, $userId = null)
    {
        return self::recordEntry($transactionDate, $referenceType, $referenceId, $accountType, $accountId, $currencyId, $amount, 0, $userId);
    }

    /**
     * Helper to decrease balance (Credit)
     */
    public static function subtractBalance($transactionDate, $referenceType, $referenceId, $accountType, $accountId, $currencyId, $amount, $userId = null)
    {
        return self::recordEntry($transactionDate, $referenceType, $referenceId, $accountType, $accountId, $currencyId, 0, $amount, $userId);
    }

    /**
     * Get current computed balance from ledger
     */
    public static function getBalance($accountType, $accountId, $currencyId)
    {
        $latest = LedgerEntry::where('account_type', $accountType)
            ->where('account_id', $accountId)
            ->where('currency_id', $currencyId)
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? $latest->running_balance : 0;
    }

    /**
     * Check if an adjustment exceeds the customer's credit limit
     */
    public static function checkCreditLimit($customerId, $currencyId, $adjustment)
    {
        $limit = \App\CustomerCreditLimit::where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->first();

        if (!$limit)
            return null;

        $currentBalance = self::getBalance('customer', $customerId, $currencyId);
        $newBalance = $currentBalance + $adjustment;

        // Assuming Negative Balance = Customer Owes Us (Asset)
        if ($newBalance < 0 && abs($newBalance) > $limit->credit_limit) {
            return [
                'exceeded' => true,
                'enforcement' => $limit->enforcement,
                'limit' => $limit->credit_limit,
                'current' => $currentBalance,
                'new' => $newBalance
            ];
        }

        return ['exceeded' => false];
    }
}
