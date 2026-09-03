<?php
namespace App\Mail;
use Illuminate\Bus\Queueable; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Mail\Mailable; use Illuminate\Queue\SerializesModels;
class RoomApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public function __construct(public $room, public $owner) {}
    public function build()
    {
        return $this->subject('Your ApnaNest listing is approved')->view('emails.room-approve')->with([
            'roomTitle'=>$this->room->title, 'ownerName'=>$this->owner->name, 'roomId'=>$this->room->id,
            'roomAddress'=>$this->room->address, 'roomPrice'=>$this->room->rent, 'roomImage'=>$this->room->photo,
        ]);
    }
}
