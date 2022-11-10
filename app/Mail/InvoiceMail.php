<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;


    public $company;
    public $pdfpath;


    public function __construct($company, $pdfpath)
    {
        $this->company = $company;
        $this->pdfpath = $pdfpath;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.invoicetext')
            ->attach($this->pdfpath, [
                // 'as' => '',
                'mime' => 'application/pdf',
            ]);
    }
}
