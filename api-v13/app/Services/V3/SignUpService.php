<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Mail\V3\EmailCertificationMail;
use App\Models\Channel;
use App\Models\Invite;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * 注册：发邮箱验证码 → 用验证码换邀请 → 凭邀请建账号。
 *
 * 自助注册与邀请注册在「邀请」这一步汇合：
 *
 * - 邀请注册：studio 发出的邀请邮件里就带着 invite id。
 * - 自助注册：验证码比对通过后，服务端为该邮箱签发（或复用）一条 invite。
 *
 * 之后两条路都是 `POST /v3/users {invite, …}`。invite id 是只有邮箱主人才拿得到的
 * uuid，持有它就等于证明了邮箱归属。
 *
 * 与 v2 的区别（v2 的 `EmailCertificationController` / `SignUpController` 原样保留，
 * 不共用代码，见 CLAUDE.md 铁律 2）：
 *
 * - 验证码只在服务端比对，绝不回传；v2 用 `GET /v2/email-certification/{id}` 把码交给前端比。
 * - 验证码 6 位、30 分钟有效、最多试 5 次；Cache 里只存它的 sha256。
 * - 验证通过之前不写 invites 表；v2 一发码就建 invite 行。
 * - 建账号时 invite 必须存在且未被使用；v2 漏了 return，invite 校验形同虚设。
 */
class SignUpService
{
    /** 验证码有效期（分钟），与邮件模板里写的「三十分钟」一致 */
    public const CODE_TTL_MINUTES = 30;

    /** 同一个验证码最多试几次，超过作废 */
    public const CODE_MAX_ATTEMPTS = 5;

    /** 存在对应模板的语言（`resources/views/emails/certification/*`），其余回落 en */
    private const MAIL_LOCALES = ['en', 'en-US', 'zh-Hans', 'zh-Hant'];

    /** invites.status：已发出、未使用 */
    private const INVITE_PENDING = 'invited';

    /** invites.status：已用于注册（与 v2 的取值一致） */
    private const INVITE_USED = 'sign-up';

    public function __construct(private readonly PasswordHasher $hasher) {}

    /**
     * 给邮箱发验证码。重复申请会作废上一个码。
     *
     * 邮箱是否已注册由 FormRequest 先挡掉。
     *
     * @throws BusinessException 邮件发送失败（503）
     */
    public function sendCode(string $email, ?string $lang): void
    {
        $code = (string) random_int(100000, 999999);
        Cache::put($this->codeKey($email), [
            'digest' => hash('sha256', $code),
            'attempts' => 0,
        ], now()->addMinutes(self::CODE_TTL_MINUTES));

        $locale = in_array($lang, self::MAIL_LOCALES, true) ? $lang : 'en';

        try {
            Mail::to($email)->send(new EmailCertificationMail($code, $locale));
        } catch (Throwable $e) {
            Log::error('v3 email certification mail failed', ['message' => $e->getMessage()]);

            throw new BusinessException(__('messages.mail_send_failed'), 503, 'mail-send-failed');
        }
    }

    /**
     * 比对验证码，通过则为该邮箱签发一条可用的 invite。
     *
     * 该邮箱已有 invite（例如 studio 早先邀请过）时复用那一行，保留原邀请人。
     *
     * @throws ValidationException 验证码不对、过期或试错次数用完
     */
    public function verifyCode(string $email, string $code): Invite
    {
        $key = $this->codeKey($email);
        $entry = Cache::get($key);

        if (! is_array($entry) || ! hash_equals($entry['digest'], hash('sha256', $code))) {
            if (is_array($entry)) {
                $attempts = $entry['attempts'] + 1;
                $attempts >= self::CODE_MAX_ATTEMPTS
                    ? Cache::forget($key)
                    : Cache::put($key, [...$entry, 'attempts' => $attempts], now()->addMinutes(self::CODE_TTL_MINUTES));
            }

            throw ValidationException::withMessages(['code' => __('messages.sign_up_code_invalid')]);
        }

        Cache::forget($key);

        $invite = Invite::whereRaw('lower(email) = ?', [mb_strtolower($email)])->first()
            ?? (new Invite)->forceFill([
                'id' => (string) Str::uuid(),
                'email' => $email,
                'user_uid' => config('mint.admin.root_uuid'),
            ]);
        $invite->status = self::INVITE_PENDING;
        $invite->save();

        return $invite;
    }

    /**
     * 查一条可用的 invite（邀请注册页用它显示邮箱）。已用过或不存在都 404。
     */
    public function findPendingInvite(string $id): Invite
    {
        $invite = Invite::where('id', $id)->where('status', self::INVITE_PENDING)->first();
        if (! $invite) {
            abort(404, __('messages.invite_invalid'));
        }

        return $invite;
    }

    /**
     * 凭 invite 建账号：user_infos 一行 + 一个私有的 draft 译文 channel，invite 标为已用。
     *
     * 与 v2 `SignUpController@store` 建出的数据形状一致（role=basic、draft channel
     * status=5），v4 读这些行不会有差别。
     *
     * @param  array{invite: string, username: string, nickname?: string|null, password: string, lang: string}  $input
     *
     * @throws ValidationException invite 不可用，或其邮箱已被注册
     */
    public function register(array $input): UserInfo
    {
        $invite = Invite::where('id', $input['invite'])->where('status', self::INVITE_PENDING)->first();
        if (! $invite) {
            throw ValidationException::withMessages(['invite' => __('messages.invite_invalid')]);
        }
        if ($this->emailRegistered($invite->email)) {
            throw ValidationException::withMessages(['invite' => __('messages.email_registered')]);
        }

        return DB::transaction(function () use ($invite, $input): UserInfo {
            $nowMs = now()->getTimestampMs();
            $nickname = trim((string) ($input['nickname'] ?? ''));

            $user = (new UserInfo)->forceFill([
                'userid' => (string) Str::uuid(),
                'username' => $input['username'],
                'nickname' => $nickname !== '' ? $nickname : $input['username'],
                'email' => $invite->email,
                'password' => $this->hasher->hash($input['password']),
                'role' => json_encode(['basic']),
                'create_time' => $nowMs,
                'modify_time' => $nowMs,
            ]);
            $user->save();

            (new Channel)->forceFill([
                'id' => app('snowflake')->id(),
                'uid' => (string) Str::uuid(),
                'name' => 'draft',
                'owner_uid' => $user->userid,
                'type' => 'translation',
                'lang' => $input['lang'],
                'status' => 5,
                'editor_id' => $user->id,
                'create_time' => $nowMs,
                'modify_time' => $nowMs,
            ])->save();

            $invite->status = self::INVITE_USED;
            $invite->save();

            return $user;
        });
    }

    /**
     * 邮箱是否已注册。user_infos.email 的唯一索引区分大小写，这里不区分：
     * `Foo@x.org` 与 `foo@x.org` 是同一个邮箱。
     */
    public function emailRegistered(string $email): bool
    {
        return UserInfo::whereRaw('lower(email) = ?', [mb_strtolower($email)])->exists();
    }

    private function codeKey(string $email): string
    {
        return 'v3:email-certification:'.hash('sha256', mb_strtolower($email));
    }
}
