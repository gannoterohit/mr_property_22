<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BrandedMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $heading,
        public string $bodyText,
        public ?string $eyebrow = null,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public array $details = [],
        public string $tone = 'primary',
        public ?string $notice = null,
        public ?string $imageUrl = null,
    ) {}

    public function build()
    {
        return $this->subject($this->mailSubject)->view('emails.branded-message');
    }
}
