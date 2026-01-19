<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\Log;

class EmailCertif extends Mailable
{
    use Queueable, SerializesModels;
    protected $uuid;
    protected $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $uuid, string $subject = 'wikipali email certification', string $lang = 'en-US')
    {
        //
        $this->uuid = $uuid;
        $this->lang = $lang;
        $this->subject($subject);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // 生成一个介于 1000 到 9999 之间的随机整数
        $randomNumber = random_int(1000, 9999);
        $key = "/email/certification/" . $this->uuid;
        Log::debug('email certification', ['key' => $key, 'value' => $randomNumber]);
        RedisClusters::put($key, $randomNumber,  30 * 60);
        return $this->view('emails.certification.' . $this->lang)
            ->with([
                'code' => $randomNumber,
            ]);
    }
}
