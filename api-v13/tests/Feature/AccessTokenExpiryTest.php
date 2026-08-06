<?php

use App\Models\AccessToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('signs channel access tokens with an expiry', function () {
    $owner = makeStudio('tester');
    $channel = makeChannel($owner);

    $token = $this->postJson('/api/v2/access-token', [
        'payload' => [[
            'res_type' => 'channel',
            'res_id' => $channel,
            'power' => 'edit',
            'book' => 0,
        ]],
    ], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.count', 1)
        ->json('data.rows.0.token');

    $key = AccessToken::where('res_id', $channel)->value('token');
    $jwt = JWT::decode($token, new Key($key.$key, 'HS512'));

    // 修复前 payload 里没有 exp，签出的 token 永久有效
    expect($jwt->exp)->toBeGreaterThan(time());
    expect($jwt->exp)->toBeLessThanOrEqual(time() + 60 * 60 * 24 * 7);
    expect($jwt->res_id)->toBe($channel);
});

it('returns an empty row set when the user cannot edit the channel', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $stranger = makeStudio('stranger');

    // 无权时该条被静默跳过——客户端必须靠 count 判空，不能当成功
    $this->postJson('/api/v2/access-token', [
        'payload' => [[
            'res_type' => 'channel',
            'res_id' => $channel,
            'power' => 'edit',
            'book' => 0,
        ]],
    ], authHeader($stranger))
        ->assertOk()
        ->assertJsonPath('data.count', 0);
});
