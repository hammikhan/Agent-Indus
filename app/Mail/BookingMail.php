<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $order;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct($order, $pdfPath)
    {
        $this->order = $order;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Recipt - ' . $this->order['pnrCode'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.booking-email2',
            with: ['order' => $this->order]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $customer_data = json_decode($this->order['customer_data'],true);
        $passenger_name = $customer_data['passengers'][0]['name']. ' ' .$customer_data['passengers'][0]['sur_name'];
        return [
            Attachment::fromPath($this->pdfPath)
                      ->as($passenger_name.'-'.$this->order['pnrCode'].'.pdf')
                      ->withMime('application/pdf'),
        ];
    }
}
