<?php
namespace App\Mail;
use Illuminate\Bus\Queueable; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Mail\Mailable; use Illuminate\Mail\Mailables\Content; use Illuminate\Mail\Mailables\Envelope; use Illuminate\Queue\SerializesModels;
class NewRoomInCityAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public function __construct(public $room, public $city) {}
    public function envelope(): Envelope { return new Envelope(subject: 'New room available in '.$this->city); }
    public function content(): Content { return new Content(view: 'emails.new-room-alert'); }
    public function attachments(): array { return []; }
}
