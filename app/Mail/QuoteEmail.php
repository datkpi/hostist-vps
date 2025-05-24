<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $pdfContent)
    {
        $this->data = $data;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Truyền trực tiếp các biến vào view thay vì truyền mảng data
        return $this->subject('Báo giá #' . $this->data['quoteNumber'] . ' - ' . $this->data['companyName'])
                    ->view('emails.quote', [
                        'quoteNumber' => $this->data['quoteNumber'],
                        'userName' => $this->data['userName'],
                        'userEmail' => $this->data['userEmail'],
                        'userAddress' => $this->data['userAddress'],
                        'companyName' => $this->data['companyName'],
                        'companyEmail' => $this->data['companyEmail'],
                        'companyPhone' => $this->data['companyPhone'],
                        'quoteDate' => $this->data['quoteDate'],
                        'expireDate' => $this->data['expireDate'],
                        'subtotal' => $this->data['subtotal'],
                        'total' => $this->data['total'],
                        'message' => $this->data['message'],
                        'items' => $this->data['items']
                    ])
                    ->attachData($this->pdfContent, 'bao-gia-' . date('Ymd') . '.pdf');
    }
}
