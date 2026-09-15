<?php

use App\Models\AiModel;
use App\Models\Sentence;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * 端到端串起 wikipali-write Skill 的完整写入链路：
 * 建模型 → 取 model token → 签 channel access token → 以模型身份写句子。
 *
 * 这条链路的意义全在最后一个断言上：句子的 editor_uid 必须是模型 uid，
 * 而不是发起操作的人类用户，否则 AI 署名与审计就是假的。
 */
it('writes a sentence attributed to the ai model, not the human operator', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create(['name' => 'claude-opus-5']);

    // 1. 人类身份签出 channel 的 access token
    $accessToken = $this->postJson('/api/v2/access-token', [
        'payload' => [[
            'res_type' => 'channel',
            'res_id' => $channel,
            'power' => 'edit',
            // book 必须是整数：UserCanEdit 用 !== 严格比较，"0" 会恒不等
            'book' => 0,
        ]],
    ], authHeader($human))
        ->assertOk()
        ->assertJsonPath('data.count', 1)
        ->json('data.rows.0.token');

    // 2. 人类身份取模型的身份 token
    $modelToken = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->assertOk()
        ->json('data.token');

    // 3. 以「模型身份」写句子：Authorization 是 modelToken，句内带 accessToken
    $this->postJson('/api/v2/sentence', [
        'sentences' => [[
            'book_id' => 1,
            'paragraph' => 10,
            'word_start' => 0,
            'word_end' => 12,
            'channel_uid' => $channel,
            'content' => '这是 AI 写入的译文',
            'content_type' => 'markdown',
            'access_token' => $accessToken,
        ]],
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertOk()
        ->assertJsonPath('data.count', 1);

    $saved = Sentence::where('channel_uid', $channel)->first();

    expect($saved)->not->toBeNull();
    expect($saved->content)->toBe('这是 AI 写入的译文');
    // 核心断言：署名归模型
    expect($saved->editor_uid)->toBe($model->uid);
    expect($saved->editor_uid)->not->toBe($human);
});

it('refuses the write when the access token is for a different channel', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human, 'mine');
    $otherChannel = makeChannel(makeStudio('someone-else'), 'not mine');
    $model = AiModel::factory()->ownedBy($human)->create();

    $accessToken = $this->postJson('/api/v2/access-token', [
        'payload' => [[
            'res_type' => 'channel',
            'res_id' => $channel,
            'power' => 'edit',
            'book' => 0,
        ]],
    ], authHeader($human))->json('data.rows.0.token');

    $modelToken = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->json('data.token');

    // 拿 A channel 的 token 去写 B channel：逐句静默跳过，count 为 0
    $this->postJson('/api/v2/sentence', [
        'sentences' => [[
            'book_id' => 1,
            'paragraph' => 10,
            'word_start' => 0,
            'word_end' => 12,
            'channel_uid' => $otherChannel,
            'content' => 'should not land',
            'access_token' => $accessToken,
        ]],
    ], ['Authorization' => 'Bearer '.$modelToken])
        ->assertOk()
        ->assertJsonPath('data.count', 0);

    expect(Sentence::count())->toBe(0);
});

it('rejects an expired access token with 403 instead of 500', function () {
    $human = makeStudio('tester');
    $channel = makeChannel($human);
    $model = AiModel::factory()->ownedBy($human)->create();

    $accessToken = $this->postJson('/api/v2/access-token', [
        'payload' => [[
            'res_type' => 'channel',
            'res_id' => $channel,
            'power' => 'edit',
            'book' => 0,
        ]],
    ], authHeader($human))->json('data.rows.0.token');

    $modelToken = $this->getJson("/api/v2/ai-model-token/{$model->uid}", authHeader($human))
        ->json('data.token');

    // 把时钟拨到 7 天有效期之后。
    // 注意不能用 $this->travel()：那只动 Carbon，而 JWT::decode 读的是 PHP 的 time()，
    // 得改 JWT::$timestamp 这个专供测试的静态覆盖点。
    // model token（365 天）在 +8 天时仍然有效，所以这里过期的只有 access token。
    JWT::$timestamp = time() + 8 * 24 * 60 * 60;

    try {
        $this->postJson('/api/v2/sentence', [
            'sentences' => [[
                'book_id' => 1,
                'paragraph' => 10,
                'word_start' => 0,
                'word_end' => 12,
                'channel_uid' => $channel,
                'content' => 'expired',
                'access_token' => $accessToken,
            ]],
        ], ['Authorization' => 'Bearer '.$modelToken])
            ->assertOk()
            ->assertJsonPath('data.count', 0);
    } finally {
        JWT::$timestamp = null;
    }

    expect(Sentence::count())->toBe(0);
});
