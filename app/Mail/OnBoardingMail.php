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

class OnBoardingMail extends Mailable
{
    use Queueable, SerializesModels;

    private $userRawPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(private User $user, $userRawPassword)
    {
        $this->user = $user;
        $this->userRawPassword = $userRawPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Autosaas Scholarship Initiative',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.onboarding',
            with: [
                'user' => $this->user,
                'password' => $this->userRawPassword,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path('images/company-logo.png'))
                ->as('autosaas.png')
                ->withMime('image/png'),
        ];
    }
}
