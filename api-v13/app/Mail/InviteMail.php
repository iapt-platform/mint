<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Class InviteMail
 *
 * 用于发送邀请注册链接的邮件。
 * 支持多语言模板，并可自定义 Dashboard 基础地址。
 */
class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 邀请唯一标识（用于生成注册链接）
     */
    public string $uuid;

    /**
     * 邮件语言（用于选择 view 模板）
     * 例如：en、zh-Hans、zh-Hant
     */
    public string $lang;

    /**
     * Dashboard 基础访问地址
     */
    public string $dashboardUrl;

    /**
     * Create a new message instance.
     *
     * @param  string       $uuid          邀请 UUID
     * @param  string       $lang          邮件语言（默认 en）
     * @param  string|null  $dashboardUrl  Dashboard 基础地址（可选）
     */
    public function __construct(
        string $uuid,
        string $lang = 'en',
        ?string $dashboardUrl = null
    ) {
        $this->uuid = $uuid;
        $this->lang = $lang;

        // 如果未传入 Dashboard 地址，则使用配置项
        $this->dashboardUrl = $dashboardUrl
            ?: config('mint.server.dashboard_base_path');
    }

    /**
     * Get the message envelope.
     *
     * 用于定义邮件头信息（如 subject、from、reply-to 等）。
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Wikipali Invite Email',
        );
    }

    /**
     * Get the message content definition.
     *
     * 用于定义邮件内容视图及其绑定的数据。
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invite.' . $this->lang,
            with: [
                // 邀请注册链接
                'url' => $this->dashboardUrl
                    . '/anonymous/users/sign-up/'
                    . $this->uuid,
            ],
        );
    }
}
