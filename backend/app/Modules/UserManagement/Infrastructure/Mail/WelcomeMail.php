<?php

namespace App\Modules\UserManagement\Infrastructure\Mail;

use App\Modules\UserManagement\Domain\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Welcome email Mailable.
 * Rendered from resources/views/emails/welcome.blade.php.
 * Infrastructure concern: the domain only fires an event – the email
 * rendering/sending is an infrastructure detail.
 */
class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Ecommerce Boilerplate!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }
}
