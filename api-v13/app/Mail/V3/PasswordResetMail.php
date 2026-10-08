<?php

namespace App\Mail\V3;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * v3 的密码重置邮件。
 *
 * 与 v2 的 `App\Mail\ForgotPassword` 的区别：链接由调用方用服务端配置拼好传进来，
 * 不接受客户端提供的站点地址（v2 收 `dashboard` 参数原样拼进链接，攻击者可借此
 * 把 reset token 引到自己的域名）。邮件模板沿用 `emails.reset_password.*`。
 */
class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $url  完整的重置链接（含 token）
     * @param  string  $lang  模板语言，调用方保证是 `emails.reset_password.*` 里存在的那几个
     */
    public function __construct(
        public string $url,
        public string $lang,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('passwords.subject', [], $this->lang),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password.'.$this->lang,
            with: ['url' => $this->url],
        );
    }
}
