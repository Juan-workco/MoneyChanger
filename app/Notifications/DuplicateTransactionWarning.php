<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DuplicateTransactionWarning extends Notification
{
    use Queueable;

    public $transaction;
    public $duplicateOf;

    public function __construct($transaction, $duplicateOf)
    {
        $this->transaction = $transaction;
        $this->duplicateOf = $duplicateOf;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Duplicate Transaction Created',
            'message' => "Transaction {$this->transaction->transaction_code} was created via override despite matching {$this->duplicateOf->transaction_code}.",
            'transaction_id' => $this->transaction->id,
            'url' => route('transactions.show', $this->transaction->id),
        ];
    }
}
