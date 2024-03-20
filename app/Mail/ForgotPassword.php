<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $uuid;
    protected $lang;
    protected $dashboard_url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $uuid,string $lang='en-US',string $dashboard=null)
    {
        //
        $this->uuid = $uuid;
        $this->lang = $lang;
        if($dashboard && !empty($dashboard)){
            $this->dashboard_url = $dashboard;
        }else{
            $this->dashboard_url = config('mint.server.dashboard_base_path');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reset_password.'.$this->lang)
                    ->with([
                        'url' => $this->dashboard_url.'/anonymous/users/reset-password/'.$this->uuid,
                    ]);
    }
}
