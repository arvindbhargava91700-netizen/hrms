<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $customer;
    public Invoice $invoice;
    public Package $package;
    public string $type;

    /**
     * Create a new message instance.
     */
    public function __construct(User $customer, Invoice $invoice, Package $package, string $type = 'due')
    {
        $this->customer = $customer;
        $this->invoice = $invoice;
        $this->package = $package;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'due' 
            ? 'Payment Overdue - ' . config('app.name') 
            : 'Upcoming Autopay Reminder - ' . config('app.name');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->type === 'due' 
            ? 'emails.customer.payment_due' 
            : 'emails.customer.next_payment';

        return new Content(
            view: $view,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
