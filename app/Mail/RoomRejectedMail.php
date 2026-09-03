<?php
namespace App\Mail;
use Illuminate\Bus\Queueable; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Mail\Mailable; use Illuminate\Queue\SerializesModels;
class RoomRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public function __construct(public $room, public $owner, public $reasons) {}
    public function build()
    {
        return $this->subject('Action required for your ApnaNest listing')->view('emails.room-rejected')->with([
            'roomTitle'=>$this->room->title, 'ownerName'=>$this->owner->name, 'roomId'=>$this->room->id,
            'roomAddress'=>$this->room->address, 'roomPrice'=>$this->room->rent,
            'roomImage'=>$this->room->photo ?? (is_array($this->room->photos) && count($this->room->photos) ? $this->room->photos[0] : null),
            'reasons'=>$this->reasons,
        ]);
    }
}
