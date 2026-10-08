<?php

use App\Mail\V3\EmailCertificationMail;
use App\Models\Channel;
use App\Models\Invite;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 注册。这里断言的是形状而非契约（spectator 未安装）。
 *
 * 端点：
 *   POST /api/v3/email-certifications   发 6 位验证码
 *   POST /api/v3/invites                验证码换 invite（服务端比对）
 *   GET  /api/v3/invites/{invite}       查看可用的 invite（邀请注册页）
 *   POST /api/v3/users                  凭 invite 建账号
 */

// 测试环境的 cache 是 array，每条测试重建应用时清零，验证码与限流计数不会串
beforeEach(fn () => Mail::fake());

/**
 * 发一次验证码，从邮件里取回明文。
 */
function requestSignUpCode(string $email): string
{
    test()->postJson('/api/v3/email-certifications', ['email' => $email])->assertNoContent();

    $code = null;
    Mail::assertSent(EmailCertificationMail::class, function (EmailCertificationMail $mail) use ($email, &$code) {
        if ($mail->hasTo($email)) {
            $code = $mail->code;
        }

        return true;
    });

    return $code;
}

function makeInvite(string $email, string $status = 'invited', ?string $inviter = null): string
{
    $id = (string) Str::uuid();
    (new Invite)->forceFill([
        'id' => $id,
        'email' => $email,
        'status' => $status,
        'user_uid' => $inviter ?? (string) Str::uuid(),
    ])->save();

    return $id;
}

/**
 * @return array<string, string>
 */
function signUpPayload(string $invite, array $overrides = []): array
{
    return array_merge([
        'invite' => $invite,
        'username' => 'new_user_1',
        'password' => 'secret-pw',
        'password_confirmation' => 'secret-pw',
        'lang' => 'zh-Hans',
    ], $overrides);
}

// ── email-certifications ─────────────────────────────────────────────

it('mails a 6 digit code and never returns it', function () {
    $response = $this->postJson('/api/v3/email-certifications', ['email' => 'fresh@example.test', 'lang' => 'zh-Hant'])
        ->assertNoContent();

    expect($response->getContent())->toBe('');
    Mail::assertSent(EmailCertificationMail::class, fn (EmailCertificationMail $mail) => $mail->hasTo('fresh@example.test')
        && preg_match('/^\d{6}$/', $mail->code) === 1
        && $mail->lang === 'zh-Hant');
});

it('refuses to send a code to a registered email, case-insensitively', function () {
    makeStudio('taken');

    $this->postJson('/api/v3/email-certifications', ['email' => 'TAKEN@example.test'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['email']]);

    Mail::assertNothingSent();
});

it('rejects a malformed email', function () {
    $this->postJson('/api/v3/email-certifications', ['email' => 'nope'])->assertStatus(422);
});

it('throttles codes for the same email', function () {
    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v3/email-certifications', ['email' => 'flood@example.test'])->assertNoContent();
    }

    $this->postJson('/api/v3/email-certifications', ['email' => 'flood@example.test'])->assertStatus(429);
});

// ── invites ──────────────────────────────────────────────────────────

it('exchanges a correct code for an invite, once', function () {
    $code = requestSignUpCode('verify@example.test');

    $data = $this->postJson('/api/v3/invites', ['email' => 'verify@example.test', 'code' => $code])
        ->assertCreated()
        ->json('data');

    expect($data)->toHaveKeys(['id', 'email', 'status', 'created_at'])
        ->and($data['email'])->toBe('verify@example.test')
        ->and($data['status'])->toBe('invited')
        ->and($data)->not->toHaveKey('user_uid')
        ->and(Invite::where('id', $data['id'])->exists())->toBeTrue();

    // 验证码一次性
    $this->postJson('/api/v3/invites', ['email' => 'verify@example.test', 'code' => $code])
        ->assertStatus(422);
});

it('rejects a wrong code without creating an invite', function () {
    requestSignUpCode('wrong@example.test');

    $this->postJson('/api/v3/invites', ['email' => 'wrong@example.test', 'code' => '000000'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['code']]);

    expect(Invite::where('email', 'wrong@example.test')->exists())->toBeFalse();
});

it('burns the code after too many wrong attempts', function () {
    $code = requestSignUpCode('brute@example.test');
    $wrong = $code === '111111' ? '222222' : '111111';

    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v3/invites', ['email' => 'brute@example.test', 'code' => $wrong])->assertStatus(422);
    }

    $this->postJson('/api/v3/invites', ['email' => 'brute@example.test', 'code' => $code])->assertStatus(422);
});

it('reuses an existing studio invite and keeps its inviter', function () {
    $inviter = makeStudio('inviter');
    $id = makeInvite('invited@example.test', 'invited', $inviter);
    $code = requestSignUpCode('invited@example.test');

    $data = $this->postJson('/api/v3/invites', ['email' => 'invited@example.test', 'code' => $code])
        ->assertOk()
        ->json('data');

    expect($data['id'])->toBe($id)
        ->and(Invite::find($id)->user_uid)->toBe($inviter);
});

it('shows a pending invite', function () {
    $id = makeInvite('show@example.test');

    $this->getJson("/api/v3/invites/{$id}")
        ->assertOk()
        ->assertJsonPath('data.email', 'show@example.test');
});

it('hides used, unknown and malformed invites', function () {
    $used = makeInvite('used@example.test', 'sign-up');

    $this->getJson("/api/v3/invites/{$used}")->assertNotFound();
    $this->getJson('/api/v3/invites/'.Str::uuid())->assertNotFound();
    $this->getJson('/api/v3/invites/not-a-uuid')->assertNotFound();
});

// ── users ────────────────────────────────────────────────────────────

it('registers an account the same shape v2 would', function () {
    $invite = makeInvite('member@example.test');

    $data = $this->postJson('/api/v3/users', signUpPayload($invite))
        ->assertCreated()
        ->json('data');

    expect($data)->toHaveKeys(['id', 'username', 'nickname', 'email', 'created_at'])
        ->and($data)->not->toHaveKey('password')
        ->and($data['email'])->toBe('member@example.test')
        ->and($data['nickname'])->toBe('new_user_1');

    $user = UserInfo::where('userid', $data['id'])->first();
    expect($user->password)->toBe(md5('secret-pw'))
        ->and(json_decode($user->role, true))->toBe(['basic']);

    $draft = Channel::where('owner_uid', $user->userid)->first();
    expect($draft->name)->toBe('draft')
        ->and($draft->status)->toBe(5)
        ->and($draft->lang)->toBe('zh-Hans')
        ->and((int) $draft->editor_id)->toBe($user->id);

    expect(Invite::find($invite)->status)->toBe('sign-up');

    // v2 登录认这个账号（v4 / mobile 不受影响）
    $this->postJson('/api/v2/sign-in', ['username' => 'new_user_1', 'password' => 'secret-pw'])
        ->assertJsonPath('ok', true);
});

it('uses the given nickname', function () {
    $invite = makeInvite('nick@example.test');

    $this->postJson('/api/v3/users', signUpPayload($invite, ['nickname' => '  觉音  ']))
        ->assertCreated()
        ->assertJsonPath('data.nickname', '觉音');
});

it('rejects a used invite', function () {
    $invite = makeInvite('twice@example.test');
    $this->postJson('/api/v3/users', signUpPayload($invite))->assertCreated();

    $this->postJson('/api/v3/users', signUpPayload($invite, ['username' => 'another_1']))
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['invite']]);
});

it('rejects an invite whose email is already registered', function () {
    makeStudio('already');
    $invite = makeInvite('already@example.test');

    $this->postJson('/api/v3/users', signUpPayload($invite))
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['invite']]);
});

it('rejects a taken username', function () {
    makeStudio('new_user_1');
    $invite = makeInvite('dup@example.test');

    $this->postJson('/api/v3/users', signUpPayload($invite))
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['username']]);
});

it('rejects malformed account fields', function (array $overrides, string $field) {
    $invite = makeInvite('bad@example.test');

    $this->postJson('/api/v3/users', signUpPayload($invite, $overrides))
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => [$field]]);

    expect(Invite::find($invite)->status)->toBe('invited');
})->with([
    'username with non-ascii' => [['username' => 'abc中文def'], 'username'],
    'username too short' => [['username' => 'abc'], 'username'],
    'password mismatch' => [['password_confirmation' => 'other-pw'], 'password'],
    'password too short' => [['password' => '12345', 'password_confirmation' => '12345'], 'password'],
    'missing lang' => [['lang' => null], 'lang'],
    'invite not a uuid' => [['invite' => 'abc'], 'invite'],
]);

it('walks the whole self sign-up flow', function () {
    $code = requestSignUpCode('walk@example.test');
    $invite = $this->postJson('/api/v3/invites', ['email' => 'walk@example.test', 'code' => $code])
        ->assertCreated()
        ->json('data.id');

    $this->postJson('/api/v3/users', signUpPayload($invite, ['username' => 'walker_1']))
        ->assertCreated()
        ->assertJsonPath('data.email', 'walk@example.test');
});
