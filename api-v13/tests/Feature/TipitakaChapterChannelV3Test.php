<?php

use App\Facades\RedisCache;
use App\Models\Channel;
use App\Models\GroupMember;
use App\Models\ProgressChapter;
use App\Models\Share;
use App\Models\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * GET /v3/tipitaka-chapter-channels/{book}/{para} —— 一章有哪些 channel 及完成度。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function makeChapterChannelRow(string $channelUid, float $progress, int $book = 9101, int $para = 5): ProgressChapter
{
    $row = new ProgressChapter;
    $row->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'book' => $book,
        'para' => $para,
        'lang' => 'zh-Hans',
        'channel_id' => $channelUid,
        'progress' => $progress,
        'all_trans' => 0,
        'public' => 0,
    ])->save();

    return $row;
}

function makePrivateChannel(string $ownerUid, string $name): string
{
    $uid = makeChannel($ownerUid, $name);
    Channel::where('uid', $uid)->update(['status' => 10]);

    return $uid;
}

function shareChannelTo(string $channelUid, string $cooperatorUid): void
{
    (new Share)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'res_id' => $channelUid,
        'res_type' => 2,
        'cooperator_id' => $cooperatorUid,
        'cooperator_type' => 0,
        'power' => 10,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();
}

it('lists channels of a chapter ordered by progress', function () {
    $owner = makeStudio('cc-owner');
    $low = makeChannel($owner, 'low');
    $high = makeChannel($owner, 'high');
    makeChapterChannelRow($low, 0.2);
    $top = makeChapterChannelRow($high, 0.9);
    makeChapterChannelRow($high, 0.5, 9101, 6);   // 别的章节

    View::forceCreate(['id' => (string) Str::uuid(), 'target_id' => $top->uid, 'target_type' => 'chapter', 'user_ip' => '127.0.0.1']);
    View::forceCreate(['id' => (string) Str::uuid(), 'target_id' => $top->uid, 'target_type' => 'chapter', 'user_ip' => '127.0.0.1']);
    RedisCache::put("/chapter_dynamic/9101/5/ch_{$high}", [0, 3, 1]);

    $json = $this->getJson('/api/v3/tipitaka-chapter-channels/9101/5')->assertOk()->json();

    expect($json['data'])->toHaveCount(2)
        ->and($json['meta']['total'])->toBe(2)
        ->and($json['data'][0])->toHaveKeys(['id', 'book', 'para', 'progress', 'progress_line', 'views', 'updated_at', 'channel', 'studio'])
        ->and($json['data'][0]['channel'])->toMatchArray(['id' => $high, 'name' => 'high', 'type' => 'translation'])
        ->and($json['data'][0]['progress'])->toBe(0.9)
        ->and($json['data'][0]['views'])->toBe(2)
        ->and($json['data'][0]['progress_line'])->toBe([0, 3, 1])
        ->and($json['data'][0]['studio'])->toMatchArray(['id' => $owner, 'nickName' => 'cc-owner'])
        ->and($json['data'][1]['channel']['id'])->toBe($low)
        ->and($json['data'][1]['progress_line'])->toBeNull();
});

it('hides private channels from anonymous and other users', function () {
    $owner = makeStudio('cc-private-owner');
    makeChapterChannelRow(makeChannel($owner, 'public'), 0.5);
    makeChapterChannelRow(makePrivateChannel($owner, 'secret'), 0.9);

    $anonymous = $this->getJson('/api/v3/tipitaka-chapter-channels/9101/5')->assertOk()->json('data');
    $stranger = $this->getJson('/api/v3/tipitaka-chapter-channels/9101/5', authHeader(makeStudio('cc-stranger')))
        ->assertOk()->json('data');

    expect(array_column(array_column($anonymous, 'channel'), 'name'))->toBe(['public'])
        ->and(array_column(array_column($stranger, 'channel'), 'name'))->toBe(['public']);
});

it('shows private channels to the owner and to users they are shared with', function () {
    $owner = makeStudio('cc-share-owner');
    $direct = makeStudio('cc-share-direct');
    $viaGroup = makeStudio('cc-share-group');
    $secret = makePrivateChannel($owner, 'secret');
    makeChapterChannelRow($secret, 0.9);

    shareChannelTo($secret, $direct);
    $groupUid = (string) Str::uuid();
    (new GroupMember)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'user_id' => $viaGroup,
        'group_id' => $groupUid,
        'power' => 10,
        'level' => 0,
        'status' => 0,
    ])->save();
    shareChannelTo($secret, $groupUid);

    foreach ([$owner, $direct, $viaGroup] as $viewer) {
        expect($this->getJson('/api/v3/tipitaka-chapter-channels/9101/5', authHeader($viewer))
            ->assertOk()->json('data.0.channel.id'))->toBe($secret);
    }
});

it('returns an empty list for a chapter without channels', function () {
    $this->getJson('/api/v3/tipitaka-chapter-channels/9101/999')
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
});

it('rejects a non-numeric path and an oversized page', function () {
    // 路径参数格式不对匹配不上路由 → 404；查询参数不对 → 422
    $this->getJson('/api/v3/tipitaka-chapter-channels/abc/5')->assertNotFound();
    $this->getJson('/api/v3/tipitaka-chapter-channels/9101/5?per_page=500')
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['per_page']]);
});

it('keeps the query count independent of the row count', function () {
    $owner = makeStudio('cc-n1');
    $countQueriesFor = function (int $rows, int $para) use ($owner): int {
        foreach (range(1, $rows) as $i) {
            makeChapterChannelRow(makeChannel(makeStudio("cc-n1-{$para}-{$i}"), "c{$i}"), $i / 100, 9102, $para);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/tipitaka-chapter-channels/9102/{$para}", authHeader($owner))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(12, 1))->toBe($countQueriesFor(2, 2));
});
