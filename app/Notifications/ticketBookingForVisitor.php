<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ticketBookingForVisitor extends Notification
{
    use Queueable;

    protected $data,$amount;
    /**
     * Create a new notification instance.
     */
    public function __construct($data,$amount)
    {

        $this->data=$data;
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
            'Congratulations! Your ticket for',
            $this->data,
            'has been successfully booked.
            The amount of ',
            $this->amount,
            'has been withdrawn from your account'
        ];
    }
}
