<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * v3 的语言协商（V3\NegotiateLocale）：按 Accept-Language 本地化 Problem Details 的
 * detail 与 errors。title 按 RFC 9457 保持稳定英文。
 *
 * 用两个不写库、不发信的失败请求做探针：
 *   POST /api/v3/sessions（缺字段）  → 422，validation.php 的文案
 *   POST /api/v3/invites（错验证码） → 422，messages.php 的文案
 */
function wrongCode(array $headers = [])
{
    return test()->postJson('/api/v3/invites', ['email' => 'locale@example.test', 'code' => '000000'], $headers);
}

it('falls back to en without Accept-Language', function () {
    wrongCode()
        ->assertStatus(422)
        ->assertJsonPath('errors.code.0', 'The verification code is incorrect or has expired.')
        ->assertHeader('Content-Language', 'en');
});

it('localizes messages and validation errors to zh-Hans', function () {
    wrongCode(['Accept-Language' => 'zh-Hans'])
        ->assertJsonPath('errors.code.0', '验证码不正确或已过期。')
        ->assertJsonPath('detail', '验证码不正确或已过期。')
        ->assertHeader('Content-Language', 'zh-Hans');

    $this->postJson('/api/v3/sessions', [], ['Accept-Language' => 'zh-Hans'])
        ->assertStatus(422)
        ->assertJsonPath('errors.login.0', 'login 字段是必填项。');
});

it('keeps the Problem title in English', function () {
    $title = wrongCode(['Accept-Language' => 'zh-Hans'])->json('title');

    expect($title)->toBe(wrongCode()->json('title'));
});

it('maps regional Chinese tags to simplified and traditional', function (string $header, string $expected) {
    wrongCode(['Accept-Language' => $header])->assertJsonPath('errors.code.0', $expected);
})->with([
    'zh-CN' => ['zh-CN', '验证码不正确或已过期。'],
    'zh-TW' => ['zh-TW', '驗證碼不正確或已過期。'],
    'zh-Hant-HK' => ['zh-Hant-HK', '驗證碼不正確或已過期。'],
]);

it('honours q values and skips unsupported languages', function () {
    wrongCode(['Accept-Language' => 'fr-FR, en;q=0.5, zh-CN;q=0.9'])
        ->assertHeader('Content-Language', 'zh-Hans');

    wrongCode(['Accept-Language' => 'fr-FR, de'])
        ->assertHeader('Content-Language', 'en');
});

it('marks responses as varying by Accept-Language', function () {
    $vary = wrongCode(['Accept-Language' => 'zh-Hans'])->headers->get('Vary');

    expect($vary)->toContain('Accept-Language');
});

it('does not set cookies', function () {
    expect(wrongCode(['Accept-Language' => 'zh-Hans'])->headers->getCookies())->toBeEmpty();
});
