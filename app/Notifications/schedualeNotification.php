<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class schedualeNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $name;

    public function __construct($title, $name)
    {
        $this->title = $title;
        $this->name = $name;
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

          'Upcoming Event Reminder',
        'Dear Visitor',
        'You have an upcoming session titled',
        $this->title,
        'in the exhibition',
            $this->name,
        ];
    }
}
