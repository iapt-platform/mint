<?php

use App\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * POST /api/v3/channels/batch-progress：一批句子在各 channel 的完成度（取代 v2 channel-progress）。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function paliOriginal(): string
{
    config(['mint.admin.root_uuid' => config('mint.admin.root_uuid') ?: (string) Str::uuid()]);
    $root = config('mint.admin.root_uuid');
    $existing = Channel::where('name', '_System_Pali_VRI_')->where('owner_uid', $root)->value('uid');

    return $existing ?? makeChannel($root, '_System_Pali_VRI_');
}

it('reports per-sentence progress for visible channels with content plus my writable ones', function () {
    $pali = paliOriginal();
    makeSentence($pali, 1, 2, 1, str_repeat('a', 10));
    makeSentence($pali, 1, 2, 5, str_repeat('b', 20));

    $me = makeStudio('me');
    $public = makeChannel(makeStudio('a'), 'public translation');
    makeSentence($public, 1, 2, 1, 'done');
    $privateOther = makeChannel(makeStudio('b'), 'private other');
    Channel::where('uid', $privateOther)->update(['status' => Channel::STATUS_PRIVATE]);
    makeSentence($privateOther, 1, 2, 1, 'secret');
    $myEmpty = makeChannel($me, 'my empty');
    Channel::where('uid', $myEmpty)->update(['status' => Channel::STATUS_PRIVATE]);

    $data = collect($this->postJson('/api/v3/channels/batch-progress', [
        'sentences' => ['1-2-1-1', '1-2-5-5'],
    ], authHeader($me))->assertOk()->json('data'))->keyBy('id');

    // 原文 channel 自己也有内容且公开，所以也会出现；这里只看关心的几个
    expect($data->has($privateOther))->toBeFalse();
    expect($data[$public]['final'])->toBe([[10, true], [20, false]]);
    expect($data[$public]['abilities']['write'])->toBeFalse();
    expect($data[$public]['content_created_at'])->not->toBeNull();
    expect($data[$myEmpty]['final'])->toBeNull();
    expect($data[$myEmpty]['abilities']['write'])->toBeTrue();
});

it('only lists a studio\'s channels when studio is given', function () {
    paliOriginal();
    [$team, $users] = makeTeam();
    $teamChannel = makeChannel($team, 'team channel');
    makeSentence($teamChannel, 1, 2, 1, 'x');
    $other = makeChannel(makeStudio('a'), 'other');
    makeSentence($other, 1, 2, 1, 'y');

    $ids = array_column($this->postJson('/api/v3/channels/batch-progress', [
        'sentences' => ['1-2-1-1'], 'studio' => $team,
    ], authHeader($users['reader']))->assertOk()->json('data'), 'id');

    expect($ids)->toBe([$teamChannel]);

    $this->postJson('/api/v3/channels/batch-progress', ['sentences' => ['1-2-1-1'], 'studio' => $team], authHeader($users['outsider']))
        ->assertForbidden();
});

it('works without login, public channels only', function () {
    paliOriginal();
    $public = makeChannel(makeStudio('a'), 'public');
    makeSentence($public, 1, 2, 1, 'x');

    expect(array_column($this->postJson('/api/v3/channels/batch-progress', ['sentences' => ['1-2-1-1']])->assertOk()->json('data'), 'id'))
        ->toContain($public);
});

it('validates the sentence list', function (array $payload, string $field) {
    $this->postJson('/api/v3/channels/batch-progress', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'missing' => [[], 'sentences'],
    'bad id' => [['sentences' => ['1-2-3']], 'sentences.0'],
    'too many' => [['sentences' => array_fill(0, 2001, '1-1-1-1')], 'sentences'],
]);

it('keeps a fixed number of queries regardless of channel count', function () {
    paliOriginal();
    $me = makeStudio('me');
    $countQueriesFor = function (int $extra) use ($me): int {
        for ($i = 0; $i < $extra; $i++) {
            makeSentence(makeChannel(makeStudio('o-'.Str::random(8))), 3, 3, 1, 'x');
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->postJson('/api/v3/channels/batch-progress', ['sentences' => ['3-3-1-1']], authHeader($me))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(6))->toBe($countQueriesFor(1));
});
