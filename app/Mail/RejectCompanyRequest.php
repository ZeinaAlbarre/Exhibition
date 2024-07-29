<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RejectCompanyRequest extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $company_name;
    public $title;

    public function __construct($company_name,$title)
    {
        $this->company_name=$company_name;
        $this->title=$title;
    }

    public function build()
    {
        return $this->markdown('emails.reject_company_request');
    }

}
