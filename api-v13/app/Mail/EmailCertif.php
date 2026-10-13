<?php

namespace App\Mail;

use App\Facades\RedisCache;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        $key = '/email/certification/'.$this->uuid;
        // 存成字符串：GET /v2/email-certification/{id} 一直返回 "1234"，v4 注册页用 === 比对表单里的字符串
        RedisCache::put($key, (string) $randomNumber, 30 * 60);

        return $this->view('emails.certification.'.$this->lang)
            ->with([
                'code' => $randomNumber,
            ]);
    }
}
