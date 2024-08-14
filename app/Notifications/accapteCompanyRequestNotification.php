<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class accapteCompanyRequestNotification extends Notification
{

    use Queueable;
    protected $exhibition_title,$message;
    /**
     * Create a new notification instance.
     */
    public function __construct($exhibition_title,$message)
    {
        $this->exhibition_title=$exhibition_title;
        $this->message=$message;
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
            $this->message,
            $this->exhibition_title,
        ];
    }
}
