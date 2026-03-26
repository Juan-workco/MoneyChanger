<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LedgerEntry;
use App\DailyBalance;
use App\DayClose;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerformDayClose extends Command
{
    protected $signature = 'ledger:day-close {--date= : The date to close (Y-m-d), defaults to today}';
    protected $description = 'Perform end of day close, calculating daily balances and locking the date';

    public function handle()
    {
        $dateStr = $this->option('date') ?: Carbon::today()->format('Y-m-d');
        $date = Carbon::parse($dateStr)->format('Y-m-d');

        $this->info("Starting day close for $date...");

        $existing = DayClose::where('close_date', $date)->first();
        if ($existing && $existing->status === 'closed') {
            $this->error("Date $date is already closed.");
            return 1;
        }

        DB::beginTransaction();

        try {
            $accounts = LedgerEntry::select('account_type', 'account_id', 'currency_id')
                ->groupBy('account_type', 'account_id', 'currency_id')
                ->get();

            $insertData = [];
            foreach ($accounts as $account) {
                $latestEntry = LedgerEntry::where('account_type', $account->account_type)
                    ->where('account_id', $account->account_id)
                    ->where('currency_id', $account->currency_id)
                    ->whereDate('transaction_date', '<=', $date)
                    ->orderBy('id', 'desc')
                    ->first();

                $balance = $latestEntry ? $latestEntry->running_balance : 0;

                $insertData[] = [
                    'balance_date' => $date,
                    'account_type' => $account->account_type,
                    'account_id' => $account->account_id,
                    'currency_id' => $account->currency_id,
                    'closing_balance' => $balance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach ($insertData as $row) {
                DailyBalance::updateOrCreate(
                    [
                        'balance_date' => $row['balance_date'],
                        'account_type' => $row['account_type'],
                        'account_id' => $row['account_id'],
                        'currency_id' => $row['currency_id']
                    ],
                    [
                        'closing_balance' => $row['closing_balance']
                    ]
                );
            }

            DayClose::updateOrCreate(
                ['close_date' => $date],
                [
                    'closed_by' => 1,
                    'status' => 'closed',
                    'notes' => 'Automated day close'
                ]
            );

            DB::commit();
            $this->info("Day close for $date completed successfully.");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Day close failed: " . $e->getMessage());
            return 1;
        }
    }
}
