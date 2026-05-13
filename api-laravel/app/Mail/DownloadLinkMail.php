<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DownloadLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Purchase $purchase) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('checkout.email_subject'),
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim(config('checkout.frontend_url'), '/');

        return new Content(
            view: 'emails.download-link',
            with: [
                'imageTitle'  => $this->purchase->image->title,
                'downloadUrl' => $frontendUrl . '/checkout/success?token=' . $this->purchase->download_token,
                'expiresAt'   => $this->purchase->download_expires_at,
            ],
        );
    }
}
