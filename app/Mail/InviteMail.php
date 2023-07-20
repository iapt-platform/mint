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

    protected $uuid;
    protected $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $uuid,string $lang='en')
    {
        //
        $this->uuid = $uuid;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.invite.'.$this->lang)
                    ->with([
                        'url' => env('DASHBOARD_URL').'/anonymous/users/sign-up/'.$this->uuid,
                    ]);
    }
}
