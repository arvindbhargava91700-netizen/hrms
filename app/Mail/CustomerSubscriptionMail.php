<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $customer;
    public Subscription $subscription;
    public Package $package;

    /**
     * Create a new message instance.
     */
    public function __construct(User $customer, Subscription $subscription, Package $package)
    {
        $this->customer = $customer;
        $this->subscription = $subscription;
        $this->package = $package;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Confirmed! - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.subscription_joined',
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
