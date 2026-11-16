<?php

use App\Models\Share;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 团队文档：共享给团队的资源。
 *
 * 端点：
 *   GET    /api/v3/groups/{group}/shares           列表（成员）
 *   DELETE /api/v3/groups/{group}/shares/{share}   移出团队（团队 owner / manager，或资源拥有者）
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function shareToTeam(string $team, string $resId, int $resType = 2, int $power = 20): Share
{
    $share = new Share;
    $share->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'res_id' => $resId,
        'res_type' => $resType,
        'cooperator_id' => $team,
        'cooperator_type' => 1,
        'power' => $power,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();

    return $share;
}

it('lists what is shared with the team', function () {
    [$team, $users] = makeTeam();
    $author = makeStudio('author');
    $channel = makeChannel($author, 'shared channel');
    shareToTeam($team, $channel, 2, 20);
    shareToTeam($team, (string) Str::uuid(), 3, 10); // 资源已被删掉的文章

    $data = $this->getJson("/api/v3/groups/{$team}/shares", authHeader($users['reader']))
        ->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->json('data');

    $byType = collect($data)->keyBy('resource.type');
    expect($byType['channel'])->toMatchArray(['role' => 'editor']);
    expect($byType['channel']['resource'])->toMatchArray([
        'type' => 'channel', 'id' => $channel, 'title' => 'shared channel', 'owner_id' => $author,
    ]);
    expect($byType['channel']['resource']['owner']['id'])->toBe($author);
    expect($byType['article']['resource']['owner'])->toBeNull();
    expect($byType['article']['role'])->toBe('reader');
    expect($byType['article']['resource']['title'])->toBeNull();
    expect($byType['article']['id'])->toBeString();
});

it('hides the team documents from outsiders', function () {
    [$team, $users] = makeTeam();

    $this->getJson("/api/v3/groups/{$team}/shares", authHeader($users['outsider']))->assertForbidden();
    $this->getJson("/api/v3/groups/{$team}/shares")->assertUnauthorized();
});

it('does not query per share', function () {
    [$team, $users] = makeTeam();
    $countQueriesFor = function (int $extra) use ($team, $users): int {
        for ($i = 0; $i < $extra; $i++) {
            shareToTeam($team, makeChannel(makeStudio('a-'.Str::random(8))));
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/groups/{$team}/shares", authHeader($users['reader']))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(6))->toBe($countQueriesFor(1));
});

it('lets team managers remove a share', function (string $actor) {
    [$team, $users] = makeTeam();
    $share = shareToTeam($team, makeChannel(makeStudio('author')));

    $this->deleteJson("/api/v3/groups/{$team}/shares/{$share->id}", [], authHeader($users[$actor]))
        ->assertNoContent();

    $this->assertModelMissing($share);
})->with(['owner', 'manager']);

it('lets the resource owner take it back even without managing the team', function () {
    [$team, $users] = makeTeam();
    $share = shareToTeam($team, makeChannel($users['reader']));

    $this->deleteJson("/api/v3/groups/{$team}/shares/{$share->id}", [], authHeader($users['reader']))
        ->assertNoContent();

    $this->assertModelMissing($share);
});

it('does not let editors remove someone else\'s share', function () {
    [$team, $users] = makeTeam();
    $share = shareToTeam($team, makeChannel(makeStudio('author')));

    $this->deleteJson("/api/v3/groups/{$team}/shares/{$share->id}", [], authHeader($users['editor']))
        ->assertForbidden();

    expect(Share::find($share->id))->not->toBeNull();
});

it('returns 404 for a share of another team', function () {
    [$team, $users] = makeTeam();
    [$otherTeam] = makeTeam();
    $share = shareToTeam($otherTeam, makeChannel(makeStudio('author')));

    $this->deleteJson("/api/v3/groups/{$team}/shares/{$share->id}", [], authHeader($users['owner']))
        ->assertNotFound();
});
