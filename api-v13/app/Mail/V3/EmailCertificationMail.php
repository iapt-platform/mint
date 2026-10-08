<?php

namespace App\Mail\V3;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * v3 的注册验证码邮件。
 *
 * 与 v2 的 `App\Mail\EmailCertif` 的区别：验证码由调用方生成并保管，邮件只负责送达。
 * v2 在 `build()` 里生成验证码并写 Cache，渲染一次邮件就换一个码。
 * 邮件模板沿用 `emails.certification.*`。
 */
class EmailCertificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $code  明文验证码
     * @param  string  $lang  模板语言，调用方保证是 `emails.certification.*` 里存在的那几个
     */
    public function __construct(
        public string $code,
        public string $lang,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('auth.email_certification_subject', [], $this->lang),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certification.'.$this->lang,
            with: ['code' => $this->code],
        );
    }
}
