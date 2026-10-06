<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerKycStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $partner;
    public string $status;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $partner, string $status, ?string $reason = null)
    {
        $this->partner = $partner;
        $this->status = $status;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved' 
            ? 'KYC Approved - ' . config('app.name') 
            : 'KYC Update Required - ' . config('app.name');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->status === 'approved' 
            ? 'emails.partner.kyc_approved' 
            : 'emails.partner.kyc_rejected';

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
