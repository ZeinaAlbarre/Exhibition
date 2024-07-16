<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcceptExhibitionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name,$title;

    public function __construct($name,$title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function build()
    {
        return $this->markdown('emails.accept_exhibition');
    }
}
