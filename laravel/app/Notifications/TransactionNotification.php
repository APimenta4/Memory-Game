<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class TransactionNotification extends Notification
{
    protected $transaction;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

/**
     * Get the array representation of the notification for database storage.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'transaction_datetime' => $this->transaction->transaction_datetime,
            'type' => $this->transaction->type,
            'euros' => $this->transaction->euros,
            'brain_coins' => $this->transaction->brain_coins,
            'payment_type' => $this->transaction->payment_type,
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }
}
