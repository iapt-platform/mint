<?php

use App\Models\Channel;
use App\Services\OpenSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 按 channel 取正文的两个 v3 接口要判 channel 的读权限：
 *   GET /api/v3/tipitaka-reading/{channel}
 *   GET /api/v3/search/{id}（整章正文文档 tipitaka_chapter_{book}-{para}_{channel}）
 *
 * 私有 channel 只有拥有者、团队成员、被共享的人能读；公开与「公开不列出」谁都能读。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function contentChannel(string $ownerUid, int $status): string
{
    $uid = makeChannel($ownerUid, 'content '.Str::random(6));
    Channel::where('uid', $uid)->update(['status' => $status]);

    return $uid;
}

/** 让 search/{id} 不真的去连 OpenSearch：被放行时返回一条最小文档 */
function fakeSearchDocument(): void
{
    test()->mock(OpenSearchService::class)
        ->shouldReceive('get')
        ->andReturn(['_id' => 'doc', '_source' => [
            'id' => 'doc', 'resource_id' => 'res', 'resource_type' => 'chapter', 'language' => 'zh',
            'updated_at' => '2026-01-01T00:00:00Z',
            'title' => ['text' => ['zh' => '标题']], 'content' => ['text' => ['zh' => '正文']],
        ]]);
}

/* ---------------- tipitaka-reading ---------------- */

it('reads a private channel only with access', function (string $who, int $status) {
    [$team, $users] = makeTeam();
    $channel = contentChannel($team, Channel::STATUS_PRIVATE);
    $headers = $who === 'anonymous' ? [] : authHeader($users[$who]);

    $this->getJson("/api/v3/tipitaka-reading/{$channel}", $headers)->assertStatus($status);
})->with([
    'anonymous' => ['anonymous', 403],
    'outsider' => ['outsider', 403],
    'team reader' => ['reader', 200],
    'team owner' => ['owner', 200],
]);

it('reads a private channel shared with me', function () {
    $me = makeStudio('me');
    $channel = contentChannel(makeStudio('author'), Channel::STATUS_PRIVATE);
    grantChannelShare($channel, $me, 10);

    $this->getJson("/api/v3/tipitaka-reading/{$channel}", authHeader($me))->assertOk();
});

it('reads public and unlisted channels without login', function (int $status) {
    $channel = contentChannel(makeStudio('author'), $status);

    $this->getJson("/api/v3/tipitaka-reading/{$channel}")->assertOk();
})->with(['public' => [Channel::STATUS_PUBLIC], 'unlisted' => [Channel::STATUS_UNLISTED]]);

it('returns 404 for an unknown or deleted channel', function () {
    $this->getJson('/api/v3/tipitaka-reading/'.Str::uuid())->assertNotFound();

    $owner = makeStudio('owner');
    $channel = contentChannel($owner, Channel::STATUS_PUBLIC);
    Channel::where('uid', $channel)->first()->delete();
    $this->getJson("/api/v3/tipitaka-reading/{$channel}")->assertNotFound();
});

/* ---------------- search/{id} 的整章正文 ---------------- */

it('guards whole-chapter search documents by their channel', function () {
    fakeSearchDocument();
    [$team, $users] = makeTeam();
    $private = contentChannel($team, Channel::STATUS_PRIVATE);
    $doc = "tipitaka_chapter_1-2_{$private}";

    $this->getJson("/api/v3/search/{$doc}")->assertForbidden();
    $this->getJson("/api/v3/search/{$doc}", authHeader($users['outsider']))->assertForbidden();
    $this->getJson("/api/v3/search/{$doc}", authHeader($users['reader']))->assertOk();
});

it('serves whole-chapter documents of public and unlisted channels to anyone', function (int $status) {
    fakeSearchDocument();
    $channel = contentChannel(makeStudio('author'), $status);

    $this->getJson("/api/v3/search/tipitaka_chapter_1-2_{$channel}")->assertOk();
})->with(['public' => [Channel::STATUS_PUBLIC], 'unlisted' => [Channel::STATUS_UNLISTED]]);

it('leaves other search documents alone', function () {
    fakeSearchDocument();

    $this->getJson('/api/v3/search/term_'.Str::uuid())->assertOk();
});
