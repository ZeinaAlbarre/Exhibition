<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcceptCompanyemail extends Mailable
{
    use Queueable, SerializesModels;

    public $company_name;

    /**
     * Create a new message instance.
     */
    public function __construct($company_name)
    {
        $this->company_name=$company_name;
    }

    public function build()
    {
        return $this->markdown('emails.accept_company');
    }
}
