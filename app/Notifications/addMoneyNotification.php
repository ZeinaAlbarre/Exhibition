<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class addMoneyNotification extends Notification
{
    use Queueable;
 protected $amount;
    /**
     * Create a new notification instance.
     */
    public function __construct($amount)
    {
        $this->amount=$amount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'The amount of ',
            $this->amount,
             'has been successfully added to your wallet'
        ];
    }
}
