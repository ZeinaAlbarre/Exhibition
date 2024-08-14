<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpdateNotification extends Notification
{

    protected $exhibition_id,$exhibition_title;

    public function __construct($exhibition_id,$exhibition_title)
    {
        $this->exhibition_id=$exhibition_id;
        $this->exhibition_title=$exhibition_title;
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

        'the organizer update exhibition info',
            $this->exhibition_id,
            $this->exhibition_title,
        ];
    }
}
