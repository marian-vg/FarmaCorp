<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupMail extends Mailable
{
    use Queueable, SerializesModels;

    // Añadimos $userName
    public function __construct(
        public $path,
        public $name,
        public $userName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'FarmaCorp - Respaldo de Base de Datos');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.backup',
            with: [
                'userName' => $this->userName, // Pasamos la variable a la vista
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->path)
                ->as($this->name)
                ->withMime('text/plain'),
        ];
    }
}
