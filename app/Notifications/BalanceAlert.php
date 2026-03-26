<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BalanceAlert extends Notification
{
    use Queueable;

    protected $account;
    protected $currencyCode;
    protected $currentBalance;

    public function __construct($account, $currencyCode, $currentBalance)
    {
        $this->account = $account;
        $this->currencyCode = $currencyCode;
        $this->currentBalance = $currentBalance;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'balance_alert',
            'message' => "⚠️ Low Balance Alert: {$this->account->account_name} ({$this->currencyCode}) balance dropped to " . number_format($this->currentBalance, 4) . " (threshold: " . number_format($this->account->alert_threshold, 4) . ")",
            'account_id' => $this->account->id,
            'currency' => $this->currencyCode,
            'balance' => $this->currentBalance,
            'threshold' => $this->account->alert_threshold,
        ];
    }
}
