<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class rejectCompanyRequestNotification extends Notification
{
    use Queueable;
    protected $exhibition_title,$exhibition_id;
    /**
     * Create a new notification instance.
     */
    public function __construct($exhibition_title,$exhibition_id)
    {
        $this->exhibition_title=$exhibition_title;
        $this->exhibition_id=$exhibition_id;
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
            'your  request to join the exhibition has been rejected',
            $this->exhibition_title,
            $this->exhibition_id
        ];
    }
}
