<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreditLimitBreached extends Notification
{
    use Queueable;

    public $transaction;
    public $customer;
    public $limitInfo;

    public function __construct($transaction, $customer, $limitInfo)
    {
        $this->transaction = $transaction;
        $this->customer = $customer;
        $this->limitInfo = $limitInfo;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Credit Limit Overridden',
            'message' => "Transaction {$this->transaction->transaction_code} exceeded {$this->customer->name}'s credit limit of {$this->limitInfo['limit']}.",
            'transaction_id' => $this->transaction->id,
            'url' => route('transactions.show', $this->transaction->id),
        ];
    }
}
