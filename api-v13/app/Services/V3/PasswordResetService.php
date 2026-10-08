<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Mail\V3\PasswordResetMail;
use App\Models\UserInfo;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * 找回密码：发重置邮件、凭 token 查用户、凭 token 设新密码。
 *
 * 与 v2 的 `ForgotPasswordController` / `ResetPasswordController` 共用
 * `user_infos.reset_password_token` / `reset_password_sent_at` 两列，但不共用代码
 * （CLAUDE.md 铁律 2）。两边的差别：
 *
 * - 库里只存 token 的 sha256，明文只出现在邮件里；v2 存明文 uuid。
 *   两边写同一列，后写的覆盖先写的，这正是「重新申请一次」应有的语义。
 * - token 60 分钟过期（v2 永不过期）。
 * - 邮箱不存在时同样「成功」，不泄露某个邮箱是否注册过（v2 回 404）。
 * - 重置链接用服务端配置 `mint.server.dashboard_v6_base_path` 拼，不收客户端传的站点地址。
 */
class PasswordResetService
{
    /** token 有效期（分钟） */
    public const TTL_MINUTES = 60;

    /** 存在对应模板的语言（`resources/views/emails/reset_password/*`），其余回落 en */
    private const MAIL_LOCALES = ['en', 'en-US', 'zh-Hans', 'zh-Hant'];

    public function __construct(private readonly PasswordHasher $hasher) {}

    /**
     * 给该邮箱的用户发重置邮件；邮箱未注册时什么都不做。
     *
     * @throws BusinessException 邮件发送失败（503）
     */
    public function request(string $email, ?string $lang): void
    {
        $user = UserInfo::where('email', $email)->first();
        if (! $user) {
            return;
        }

        $token = Str::random(64);
        $user->reset_password_token = $this->digest($token);
        $user->reset_password_sent_at = now();
        $user->save();

        $locale = in_array($lang, self::MAIL_LOCALES, true) ? $lang : 'en';
        $url = rtrim(config('mint.server.dashboard_v6_base_path'), '/').'/anonymous/reset-password/'.$token;

        try {
            Mail::to($user->email)->send(new PasswordResetMail($url, $locale));
        } catch (Throwable $e) {
            Log::error('v3 password reset mail failed', ['message' => $e->getMessage()]);

            // 用 BusinessException 而不是让它冒成 500：兜底处理器会抹掉 5xx 的 detail，
            // 而这条文案得让用户看到（稍后重试，而不是干等一封不会来的邮件）
            throw new BusinessException(__('messages.mail_send_failed'), 503, 'mail-send-failed');
        }
    }

    /**
     * 凭 token 找到待重置的用户，token 不存在或已过期时 404。
     *
     * @return array{username: string, expires_at: CarbonInterface}
     */
    public function find(string $token): array
    {
        $user = $this->userByToken($token);

        return [
            'username' => $user->username,
            'expires_at' => $this->expiresAt($user),
        ];
    }

    /**
     * 设新密码并作废 token（一次性）。
     */
    public function reset(string $token, string $password): void
    {
        $user = $this->userByToken($token);
        $user->password = $this->hasher->hash($password);
        $user->reset_password_token = null;
        $user->reset_password_sent_at = null;
        $user->save();
    }

    private function userByToken(string $token): UserInfo
    {
        $user = UserInfo::where('reset_password_token', $this->digest($token))
            ->whereNotNull('reset_password_sent_at')
            ->first();

        // 不存在与已过期同样回 404：不给调用方区分两者的信号
        if (! $user || $this->expiresAt($user)->isPast()) {
            abort(404, __('passwords.token'));
        }

        return $user;
    }

    private function expiresAt(UserInfo $user): CarbonInterface
    {
        // UserInfo 是 v2/v3 共享模型，没给这列配 cast，这里自己解析，不去改共享模型
        return Date::parse($user->reset_password_sent_at)->addMinutes(self::TTL_MINUTES);
    }

    private function digest(string $token): string
    {
        return hash('sha256', $token);
    }
}
