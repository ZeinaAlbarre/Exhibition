<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcceptCompanyRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $company_name;
    public $title;
    public $location;
    public $start_date;

    /**
     * Create a new message instance.
     */
    public function __construct($company_name,$title,$location,$start_date)
    {
        $this->company_name=$company_name;
        $this->title=$title;
        $this->location=$location;
        $this->start_date=$start_date;
    }

    public function build()
    {
        return $this->markdown('emails.accept_company_request');
    }
}
