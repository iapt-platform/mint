<?php

namespace App\Mail;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $invite;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $uuid)
    {
        //
        $this->invite = $uuid;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.invite')
                    ->with([
                        'url' => env('APP_URL').'/anonymous/users/sign-up/'.$this->invite,
                        'uuid' => $this->invite,
                    ]);
    }
}
