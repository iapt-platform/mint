<?php

use App\Mail\V3\PasswordResetMail;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/**
 * v3 找回密码。这里断言的是形状而非契约（spectator 未安装）。
 *
 * 端点：
 *   POST  /api/v3/password-resets           发重置邮件（任何邮箱都 204）
 *   GET   /api/v3/password-resets/{token}   查看：给哪个账号、何时过期
 *   PATCH /api/v3/password-resets/{token}   设新密码，token 作废
 */
// 测试环境的 cache 是 array，每条测试重建应用时清零，限流计数不会串到下一条
beforeEach(fn () => Mail::fake());

/**
 * 走一遍申请流程，从发出的邮件里取回明文 token。
 */
function requestResetToken(string $email): string
{
    test()->postJson('/api/v3/password-resets', ['email' => $email])->assertNoContent();

    $token = null;
    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use (&$token) {
        $token = substr($mail->url, strrpos($mail->url, '/') + 1);

        return true;
    });

    return $token;
}

it('mails a reset link pointing at dashboard-v6 and stores only a digest', function () {
    makeStudio('reset-me');

    $this->postJson('/api/v3/password-resets', ['email' => 'reset-me@example.test', 'lang' => 'zh-Hans'])
        ->assertNoContent();

    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) {
        $token = substr($mail->url, strrpos($mail->url, '/') + 1);
        $stored = UserInfo::where('username', 'reset-me')->value('reset_password_token');

        return $mail->hasTo('reset-me@example.test')
            && str_starts_with($mail->url, rtrim(config('mint.server.dashboard_v6_base_path'), '/').'/anonymous/reset-password/')
            && $mail->lang === 'zh-Hans'
            && $stored === hash('sha256', $token)
            && $stored !== $token;
    });
});

it('falls back to en for a language without a mail template', function () {
    makeStudio('reset-lang');

    $this->postJson('/api/v3/password-resets', ['email' => 'reset-lang@example.test', 'lang' => 'my'])
        ->assertNoContent();

    Mail::assertSent(PasswordResetMail::class, fn (PasswordResetMail $mail) => $mail->lang === 'en');
});

it('answers 204 for an unknown email without sending anything', function () {
    $this->postJson('/api/v3/password-resets', ['email' => 'nobody@example.test'])
        ->assertNoContent();

    Mail::assertNothingSent();
});

it('rejects a malformed email', function () {
    $this->postJson('/api/v3/password-resets', ['email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonPath('errors.email.0', fn ($m) => is_string($m));
});

it('ignores a client supplied dashboard url', function () {
    makeStudio('reset-host');

    $this->postJson('/api/v3/password-resets', [
        'email' => 'reset-host@example.test',
        'dashboard' => 'https://evil.example',
    ])->assertNoContent();

    Mail::assertSent(PasswordResetMail::class, fn (PasswordResetMail $mail) => ! str_contains($mail->url, 'evil.example'));
});

it('throttles repeated requests for the same email', function () {
    makeStudio('reset-flood');

    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v3/password-resets', ['email' => 'reset-flood@example.test'])->assertNoContent();
    }

    $this->postJson('/api/v3/password-resets', ['email' => 'reset-flood@example.test'])->assertStatus(429);
});

it('shows which account a token resets', function () {
    makeStudio('reset-show');
    $token = requestResetToken('reset-show@example.test');

    $data = $this->getJson("/api/v3/password-resets/{$token}")
        ->assertOk()
        ->json('data');

    expect($data)->toHaveKeys(['username', 'expires_at'])
        ->and($data['username'])->toBe('reset-show')
        ->and($data['expires_at'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/')
        ->and($data)->not->toHaveKey('password');
});

it('answers 404 for an unknown token', function () {
    $this->getJson('/api/v3/password-resets/'.str_repeat('a', 64))
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/problem+json');
});

it('answers 404 for an expired token', function () {
    makeStudio('reset-old');
    $token = requestResetToken('reset-old@example.test');

    $this->travel(61)->minutes();

    $this->getJson("/api/v3/password-resets/{$token}")->assertNotFound();
    $this->patchJson("/api/v3/password-resets/{$token}", [
        'password' => 'new-secret',
        'password_confirmation' => 'new-secret',
    ])->assertNotFound();
});

it('does not accept the raw uuid tokens written by v2', function () {
    $uid = makeStudio('reset-v2');
    UserInfo::where('userid', $uid)->update([
        'reset_password_token' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        'reset_password_sent_at' => now(),
    ]);

    $this->getJson('/api/v3/password-resets/'.str_repeat('a', 64))->assertNotFound();
});

it('sets the new password and burns the token', function () {
    makeStudio('reset-done');
    $token = requestResetToken('reset-done@example.test');

    $this->patchJson("/api/v3/password-resets/{$token}", [
        'password' => 'new-secret',
        'password_confirmation' => 'new-secret',
    ])->assertNoContent();

    $user = UserInfo::where('username', 'reset-done')->first();
    expect($user->password)->toBe(md5('new-secret'))
        ->and($user->reset_password_token)->toBeNull();

    // v2 登录仍认这个新密码（md5 比对），v4 / mobile 不受影响
    $this->postJson('/api/v2/sign-in', ['username' => 'reset-done', 'password' => 'new-secret'])
        ->assertJsonPath('ok', true);

    $this->getJson("/api/v3/password-resets/{$token}")->assertNotFound();
});

it('rejects a mismatched confirmation', function () {
    makeStudio('reset-typo');
    $token = requestResetToken('reset-typo@example.test');

    $this->patchJson("/api/v3/password-resets/{$token}", [
        'password' => 'new-secret',
        'password_confirmation' => 'new-secreT',
    ])->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);

    expect(UserInfo::where('username', 'reset-typo')->value('password'))->toBe('x');
});

it('rejects a too short password', function () {
    makeStudio('reset-short');
    $token = requestResetToken('reset-short@example.test');

    $this->patchJson("/api/v3/password-resets/{$token}", [
        'password' => '12345',
        'password_confirmation' => '12345',
    ])->assertStatus(422);
});

it('does not register PUT', function () {
    $this->putJson('/api/v3/password-resets/'.str_repeat('a', 64), [])->assertStatus(405);
});
