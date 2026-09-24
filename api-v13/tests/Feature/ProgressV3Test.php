<?php

use App\Models\ProgressChapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * GET /v3/progress —— 按 channel 列章节翻译进度。
 *
 * 这个端点原来零测试覆盖，整改（去掉只有一个合法值的 view 开关、加 FormRequest、
 * order 白名单、逻辑下沉 Service）时补上。
 */
function makeProgressChapter(string $channelUid, int $book, int $para, array $attrs = []): ProgressChapter
{
    $row = new ProgressChapter;
    $row->forceFill(array_merge([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'book' => $book,
        'para' => $para,
        'lang' => 'zh-Hans',
        'channel_id' => $channelUid,
        'progress' => 0.5,
        'title' => "book {$book} para {$para}",
        // all_trans / public 是 NOT NULL 且无默认值
        'all_trans' => 0,
        'public' => 0,
    ], $attrs))->save();

    return $row;
}

it('lists chapters of a channel', function () {
    $channel = makeChannel(makeStudio('prog-owner'), 'prog channel');
    makeProgressChapter($channel, 9001, 1);
    makeProgressChapter($channel, 9001, 2);
    makeProgressChapter((string) Str::uuid(), 9001, 3);   // 别的 channel

    $json = $this->getJson("/api/v3/progress?channels={$channel}")->assertOk()->json();

    expect($json['data'])->toHaveCount(2)
        ->and($json['data'][0])->toHaveKeys(['book', 'para', 'lang', 'progress', 'channel_id'])
        ->and($json['meta']['total'])->toBe(2);
});

it('accepts an underscore separated channel list', function () {
    $a = makeChannel(makeStudio('prog-a'), 'a');
    $b = makeChannel(makeStudio('prog-b'), 'b');
    makeProgressChapter($a, 9001, 1);
    makeProgressChapter($b, 9002, 1);

    // 分隔符是下划线不是逗号，这是既有契约
    $data = $this->getJson("/api/v3/progress?channels={$a}_{$b}")->assertOk()->json('data');

    expect($data)->toHaveCount(2);
});

it('filters by book and lang', function () {
    $channel = makeChannel(makeStudio('prog-filter'), 'c');
    makeProgressChapter($channel, 9001, 1, ['lang' => 'zh-Hans']);
    makeProgressChapter($channel, 9002, 1, ['lang' => 'en']);

    expect($this->getJson("/api/v3/progress?channels={$channel}&book=9001")->assertOk()->json('data'))
        ->toHaveCount(1);
    expect($this->getJson("/api/v3/progress?channels={$channel}&lang=en")->assertOk()->json('data'))
        ->toHaveCount(1);
});

it('requires the channels filter', function () {
    // 不带条件就能拉全表的列表端点是设计错误（硬规范第 4 条）
    $this->getJson('/api/v3/progress')
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['channels']]);
});

it('rejects an order column outside the whitelist', function () {
    $channel = makeChannel(makeStudio('prog-order'), 'c');

    // 原来 order 未校验，直接拼进 orderBy——传个不存在的列名就是 QueryException → 500
    $this->getJson("/api/v3/progress?channels={$channel}&order=no_such_column")
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['order']]);

    $this->getJson("/api/v3/progress?channels={$channel}&order=updated_at")->assertOk();
});

it('rejects a bad dir or per_page', function () {
    $channel = makeChannel(makeStudio('prog-bad'), 'c');

    $this->getJson("/api/v3/progress?channels={$channel}&dir=sideways")->assertStatus(422);
    $this->getJson("/api/v3/progress?channels={$channel}&per_page=100000")->assertStatus(422);
});

it('no longer needs the view parameter', function () {
    $channel = makeChannel(makeStudio('prog-view'), 'c');
    makeProgressChapter($channel, 9001, 1);

    // view 只有一个合法值，是噪音，已去掉；传了也只是被忽略
    $this->getJson("/api/v3/progress?channels={$channel}")->assertOk();
    $this->getJson("/api/v3/progress?channels={$channel}&view=channel")->assertOk();
});
