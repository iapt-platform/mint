<?php

use App\Models\Reaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 reactions：点赞/收藏/书签/关注/下载记录共用一张 likes 表。
 *
 * 端点：
 *   GET    /api/v3/reactions?target_id=…        公共，按 target 列记录
 *   GET    /api/v3/reactions/tally?target_id=…   公共，各 type 计数 + 可选我的选择
 *   GET    /api/v3/me/reactions                 我的列表（登录）
 *   POST   /api/v3/me/reactions                 我添加（登录，幂等）
 *   DELETE /api/v3/me/reactions/{reaction}       我删除（登录，只能删自己的）
 */
function makeReaction(array $attrs = []): Reaction
{
    return Reaction::forceCreate(array_merge([
        'id' => (string) Str::uuid(),
        'type' => 'like',
        'target_id' => (string) Str::uuid(),
        'target_type' => 'task',
        'user_id' => makeStudio('reaction-'.Str::random(6)),
        'context' => null,
    ], $attrs));
}

it('lists reactions of a target without auth', function () {
    $target = (string) Str::uuid();
    makeReaction(['target_id' => $target]);
    makeReaction(['target_id' => $target, 'type' => 'watch']);
    makeReaction(['target_id' => (string) Str::uuid()]);

    $data = $this->getJson("/api/v3/reactions?target_id={$target}")
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(2)
        ->each(fn ($row) => $row->toHaveKeys(['id', 'type', 'target_id', 'target_type', 'user']));
});

it('filters reactions by type', function () {
    $target = (string) Str::uuid();
    makeReaction(['target_id' => $target, 'type' => 'like']);
    makeReaction(['target_id' => $target, 'type' => 'favorite']);

    $data = $this->getJson("/api/v3/reactions?target_id={$target}&type=like")
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['type'])->toBe('like');
});

it('rejects a non-uuid target_id', function () {
    $this->getJson('/api/v3/reactions?target_id=not-a-uuid')->assertStatus(422);
});

it('rejects an out-of-range per_page', function () {
    $target = (string) Str::uuid();

    $this->getJson("/api/v3/reactions?target_id={$target}&per_page=100000")->assertStatus(422);
    $this->getJson("/api/v3/reactions?target_id={$target}&per_page=0")->assertStatus(422);
});

it('honours per_page', function () {
    $target = (string) Str::uuid();
    makeReaction(['target_id' => $target, 'type' => 'like']);
    makeReaction(['target_id' => $target, 'type' => 'watch']);

    $json = $this->getJson("/api/v3/reactions?target_id={$target}&per_page=1")
        ->assertOk()
        ->json();

    expect($json['data'])->toHaveCount(1)
        ->and($json['meta']['per_page'])->toBe(1)
        ->and($json['meta']['total'])->toBe(2);
});

it('embeds the acting user without querying per row', function () {
    // N+1 守卫：查询数必须与行数无关。
    // 现在是 4 条（分页 count + 取页 + 预加载 user_infos + 预加载 ai_models）；
    // 断言的是「2 行与 12 行一样多」而不是硬编码 4——前者是真正的不变量，
    // 后者会被 Laravel 内部实现的变动误伤。Resource 里每行再查一次就立刻红。
    $countQueriesFor = function (int $rows): int {
        $target = (string) Str::uuid();
        foreach (range(1, $rows) as $i) {
            makeReaction([
                'target_id' => $target,
                'type' => 'like',
                'user_id' => makeStudio('actor-'.Str::random(8)),
            ]);
        }

        DB::enableQueryLog();
        DB::flushQueryLog();   // enable 不清空历史，两次测量之间必须手动冲掉
        $data = $this->getJson("/api/v3/reactions?target_id={$target}&per_page=200")
            ->assertOk()
            ->json('data');
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        expect($data)->toHaveCount($rows);

        return $queries;
    };

    expect($countQueriesFor(12))->toBe($countQueriesFor(2));
});

it('resolves the acting user through the constrained eager load', function () {
    $target = (string) Str::uuid();
    $uid = makeStudio('actor-name');
    makeReaction(['target_id' => $target, 'user_id' => $uid]);

    $data = $this->getJson("/api/v3/reactions?target_id={$target}")->assertOk()->json('data');

    // 受限列漏掉哪个，这里就会露馅：id 来自 userid、nickName 来自 nickname、sn 来自 id
    expect($data[0]['user']['id'])->toBe($uid)
        ->and($data[0]['user']['nickName'])->toBe('actor-name')
        ->and($data[0]['user']['userName'])->toBe('actor-name')
        ->and($data[0]['user']['sn'])->toBeGreaterThan(0);
});

it('falls back to the unknown placeholder for an orphan user_id', function () {
    $target = (string) Str::uuid();
    Reaction::forceCreate([
        'id' => (string) Str::uuid(),
        'type' => 'like',
        'target_id' => $target,
        'target_type' => 'task',
        'user_id' => (string) Str::uuid(), // 既不在 user_infos 也不在 ai_models
        'context' => null,
    ]);

    $data = $this->getJson("/api/v3/reactions?target_id={$target}")->assertOk()->json('data');

    expect($data[0]['user']['nickName'])->toBe('unknown');
});

it('tallies counts per type without auth', function () {
    $target = (string) Str::uuid();
    makeReaction(['target_id' => $target, 'type' => 'like']);
    makeReaction(['target_id' => $target, 'type' => 'like']);
    makeReaction(['target_id' => $target, 'type' => 'favorite']);

    $data = $this->getJson("/api/v3/reactions/tally?target_id={$target}")
        ->assertOk()
        ->json('data');

    $like = collect($data)->firstWhere('type', 'like');
    $favorite = collect($data)->firstWhere('type', 'favorite');

    expect($like['count'])->toBe(2)
        ->and($like['selected'])->toBeFalse()
        ->and($favorite['count'])->toBe(1);
});

it('marks my selection in the tally when authenticated', function () {
    $target = (string) Str::uuid();
    $uid = makeStudio('tally-owner');
    $mine = makeReaction(['target_id' => $target, 'type' => 'like', 'user_id' => $uid]);
    makeReaction(['target_id' => $target, 'type' => 'like']);

    $data = $this->getJson("/api/v3/reactions/tally?target_id={$target}", authHeader($uid))
        ->assertOk()
        ->json('data');

    $like = collect($data)->firstWhere('type', 'like');
    expect($like['selected'])->toBeTrue()
        ->and($like['id'])->toBe($mine->id);
});

it('requires auth to list my reactions', function () {
    $this->getJson('/api/v3/me/reactions')->assertStatus(401);
});

it('lists only my reactions', function () {
    $uid = makeStudio('me-owner');
    makeReaction(['user_id' => $uid, 'type' => 'like']);
    makeReaction(['user_id' => $uid, 'type' => 'favorite']);
    makeReaction(['user_id' => makeStudio('other'), 'type' => 'like']);

    $data = $this->getJson('/api/v3/me/reactions', authHeader($uid))
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(2);
});

it('requires auth to add a reaction', function () {
    $this->postJson('/api/v3/me/reactions', [
        'type' => 'like',
        'target_id' => (string) Str::uuid(),
        'target_type' => 'task',
    ])->assertStatus(401);
});

it('answers 401 before 422 when not logged in', function () {
    // auth.v3 中间件在 FormRequest 之前跑，所以「未登录 + 参数非法」是 401 不是 422。
    // 这正是把登录闸放进中间件（而不是控制器第一行）买到的东西
    $this->postJson('/api/v3/me/reactions', ['type' => 'bogus'])
        ->assertStatus(401)
        ->assertJsonStructure(['type', 'title', 'status']);
});

it('does not accept the v1 cookie bypass', function () {
    $uid = makeStudio('cookie-user');

    // 必须直接改 $_COOKIE 超全局：withUnencryptedCookies() 只填 Symfony Request 的
    // cookie 袋子，不碰超全局，用它写出来的 cookie 测试是空转的（不管代码里有没有
    // 那条分支都会过）。helper 见 AuthServiceCookieTest.php
    $backup = $_COOKIE;
    $_COOKIE = ['user_uid' => $uid, 'user_id' => '1'];
    try {
        $this->getJson('/api/v3/me/reactions')->assertStatus(401);
    } finally {
        $_COOKIE = $backup;
    }
});

it('validates required fields when adding', function () {
    $uid = makeStudio('store-owner');

    $this->postJson('/api/v3/me/reactions', ['type' => 'like'], authHeader($uid))
        ->assertStatus(422);
});

it('rejects an unknown target_type when adding', function () {
    $uid = makeStudio('store-owner');

    $this->postJson('/api/v3/me/reactions', [
        'type' => 'like',
        'target_id' => (string) Str::uuid(),
        'target_type' => 'unknown',
    ], authHeader($uid))->assertStatus(422);
});

it('adds a reaction and forces user_id to the current user', function () {
    $uid = makeStudio('store-owner');
    $target = (string) Str::uuid();

    $data = $this->postJson('/api/v3/me/reactions', [
        'type' => 'like',
        'target_id' => $target,
        'target_type' => 'task',
        'user_id' => (string) Str::uuid(), // 客户端塞的 user_id 应被忽略
    ], authHeader($uid))->assertCreated()->json('data');

    // 返回的是资源本身。计数不在这里给——那是 tally 的职责
    expect($data)->toHaveKeys(['id', 'type', 'target_id', 'target_type', 'user'])
        ->and($data)->not->toHaveKey('count')
        ->and($data['type'])->toBe('like')
        ->and($data['target_id'])->toBe($target);

    $record = Reaction::find($data['id']);
    expect($record->user_id)->toBe($uid);
});

it('is idempotent: repeat add returns the same record', function () {
    $uid = makeStudio('store-owner');
    $target = (string) Str::uuid();
    $payload = ['type' => 'favorite', 'target_id' => $target, 'target_type' => 'task'];

    // 首次为 201（新建），重复提交命中唯一键，为 200（幂等不新建）
    $first = $this->postJson('/api/v3/me/reactions', $payload, authHeader($uid))->assertCreated()->json('data');
    $second = $this->postJson('/api/v3/me/reactions', $payload, authHeader($uid))->assertOk()->json('data');

    expect($second['id'])->toBe($first['id'])
        ->and(Reaction::count())->toBe(1);
});

it('requires auth to delete a reaction', function () {
    $reaction = makeReaction();

    $this->deleteJson("/api/v3/me/reactions/{$reaction->id}")->assertStatus(401);
});

it('deletes my reaction with 204 and an empty body', function () {
    $uid = makeStudio('del-owner');
    $target = (string) Str::uuid();
    $mine = makeReaction(['target_id' => $target, 'type' => 'like', 'user_id' => $uid]);
    $others = makeReaction(['target_id' => $target, 'type' => 'like']);

    // 两条都要：只有前者可能「返回 204 但压根没删」，只有后者可能「删对了但多吐了 body」
    $this->deleteJson("/api/v3/me/reactions/{$mine->id}", [], authHeader($uid))
        ->assertNoContent();
    $this->assertModelMissing($mine);

    // 别人那条还在；剩余计数去 tally 拿，不由 destroy 返回
    $this->assertModelExists($others);
    $tally = $this->getJson("/api/v3/reactions/tally?target_id={$target}")->assertOk()->json('data');
    expect(collect($tally)->firstWhere('type', 'like')['count'])->toBe(1);
});

it('forbids deleting another user reaction', function () {
    $other = makeReaction(['user_id' => makeStudio('del-other')]);

    $detail = $this->deleteJson("/api/v3/me/reactions/{$other->id}", [], authHeader(makeStudio('del-me')))
        ->assertStatus(403)
        ->json('detail');

    // 翻译文件缺键时 __() 会原样吐出键名，这条守住那种退化
    expect($detail)->not->toBe('site.forbidden')->not->toBeEmpty();

    expect(Reaction::find($other->id))->not->toBeNull();
});

it('rejects an unknown type filter on my reactions', function () {
    $this->getJson('/api/v3/me/reactions?type=bogus', authHeader(makeStudio('filter-owner')))
        ->assertStatus(422);
});
