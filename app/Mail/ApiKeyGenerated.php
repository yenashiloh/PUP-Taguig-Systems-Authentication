<?php

namespace App\Mail;

use App\Models\ApiKey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApiKeyGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $apiKey;
    public $rawKey;

    /**
     * Create a new message instance.
     */
    public function __construct(ApiKey $apiKey, $rawKey)
    {
        $this->apiKey = $apiKey;
        $this->rawKey = $rawKey;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'PUP-Taguig API Key Generated - ' . $this->apiKey->application_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.api-key-generated',
            with: [
                'apiKey' => $this->apiKey,
                'rawKey' => $this->rawKey,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message (for older Laravel versions compatibility)
     */
    public function build()
    {
        return $this->subject('PUP-Taguig API Key Generated - ' . $this->apiKey->application_name)
                   ->view('emails.api-key-generated')
                   ->with([
                       'apiKey' => $this->apiKey,
                       'rawKey' => $this->rawKey,
                   ]);
    }
}