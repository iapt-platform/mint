<?php

use App\Models\Discussion;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 discussions：评论线程（话题 + 一层回复），底层是 discussions 表。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 *
 * 端点：
 *   GET    /api/v3/discussions?target_type=&target_id=     话题列表（可选登录）
 *   GET    /api/v3/discussions/summary?target_type=&target_id=  概况（可选登录）
 *   POST   /api/v3/discussions                              发起话题（登录）
 *   GET    /api/v3/discussions/{d}                          单条（可选登录，含墓碑）
 *   PATCH  /api/v3/discussions/{d}                          局部更新（登录）
 *   DELETE /api/v3/discussions/{d}                          软删除（登录）
 *   GET    /api/v3/discussions/{d}/replies                  回复列表（可选登录）
 *   POST   /api/v3/discussions/{d}/replies                  回复（登录）
 */
function makeDiscussion(array $attrs = []): Discussion
{
    return Discussion::forceCreate(array_merge([
        'res_type' => 'sentence',
        'res_id' => (string) Str::uuid(),
        'type' => 'discussion',
        'title' => 'topic',
        'content' => 'body',
        'editor_uid' => makeStudio('d-'.Str::random(8)),
    ], $attrs));
}

/**
 * 一个句子目标：channel 归 $owner 所有，返回句子 uid。
 */
function makeTargetSentence(string $owner): string
{
    return makeSentence(makeChannel($owner), 1, 1, 1, 'sentence text')->uid;
}

function makeBasicUser(string $name): string
{
    $uid = makeStudio($name);
    UserInfo::where('userid', $uid)->update(['role' => json_encode(['basic'])]);

    return $uid;
}

/* ------------------------------------------------------------------ 列表 */

it('lists only topics of the target, with reply counts and abilities', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $topic = makeDiscussion(['res_id' => $target, 'editor_uid' => $owner]);
    makeDiscussion(['res_id' => $target, 'parent' => $topic->id, 'title' => null]);
    makeDiscussion(['res_id' => $target, 'type' => 'commentary']);   // 不带 type 时不列批注
    makeDiscussion();                                                 // 别的对象

    $json = $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}", authHeader($owner))
        ->assertOk()
        ->json();

    expect($json['data'])->toHaveCount(1)
        ->and($json['data'][0])->toHaveKeys(['id', 'target_type', 'target_id', 'parent_id', 'type', 'severity', 'status', 'editor', 'abilities', 'deleted_at'])
        ->and($json['data'][0]['reply_count'])->toBe(1)
        ->and($json['data'][0]['abilities'])->toBe(['update' => true, 'delete' => true, 'close' => true, 'reply' => true])
        ->and($json['meta']['total'])->toBe(1);
});

it('hides private topics from anonymous viewers, but not issues', function () {
    $target = makeTargetSentence(makeStudio('owner'));
    makeDiscussion(['res_id' => $target, 'type' => 'discussion']);
    $issue = makeDiscussion(['res_id' => $target, 'type' => 'issue', 'severity' => 'error']);
    makeDiscussion(['res_id' => $target, 'type' => 'qa']);

    $data = $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}&sort=created_at")
        ->assertOk()
        ->json('data');

    // issue 会注入公开的阅读页，所以对匿名也可见
    expect(array_column($data, 'type'))->toEqualCanonicalizing(['issue', 'qa'])
        ->and($data[0]['abilities']['reply'])->toBeFalse();
    $this->getJson("/api/v3/discussions/{$issue->id}")->assertOk();
});

it('shows a basic user only their own collaboration topics on targets they cannot manage', function () {
    $target = makeTargetSentence(makeStudio('owner'));
    $basic = makeBasicUser('basic');
    makeDiscussion(['res_id' => $target, 'editor_uid' => $basic, 'title' => 'mine']);
    $others = makeDiscussion(['res_id' => $target, 'title' => 'others']);

    $data = $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}", authHeader($basic))
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['title'])->toBe('mine');

    // 单条与回复列表守同一条规则（v2 只在列表上判断）
    $this->getJson("/api/v3/discussions/{$others->id}", authHeader($basic))->assertNotFound();
    $this->getJson("/api/v3/discussions/{$others->id}/replies", authHeader($basic))->assertNotFound();
});

it('lets a basic user see all topics on a target they can manage', function () {
    $basic = makeBasicUser('basic-owner');
    $target = makeTargetSentence($basic);
    makeDiscussion(['res_id' => $target]);
    makeDiscussion(['res_id' => $target]);

    $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}", authHeader($basic))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('filters topics by type, status and severity', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    makeDiscussion(['res_id' => $target, 'type' => 'issue', 'severity' => 'error']);
    makeDiscussion(['res_id' => $target, 'type' => 'issue', 'severity' => 'suggestion']);
    makeDiscussion(['res_id' => $target, 'type' => 'issue', 'severity' => 'warning', 'status' => 'close']);
    makeDiscussion(['res_id' => $target]);

    $base = "/api/v3/discussions?target_type=sentence&target_id={$target}";
    $this->getJson("{$base}&type=issue", authHeader($owner))->assertJsonCount(3, 'data');
    $this->getJson("{$base}&type=issue&status=active", authHeader($owner))->assertJsonCount(2, 'data');
    $this->getJson("{$base}&severity=error,warning", authHeader($owner))->assertJsonCount(2, 'data');
});

it('rejects an invalid list query', function () {
    $target = (string) Str::uuid();

    $this->getJson('/api/v3/discussions?target_type=sentence')->assertStatus(422);
    $this->getJson("/api/v3/discussions?target_type=chapter&target_id={$target}")->assertStatus(422);
    $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}&type=feedback")->assertStatus(422);
    $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}&per_page=1000")->assertStatus(422);
    $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}&sort=title")->assertStatus(422);
});

it('keeps a deleted topic with replies as a tombstone and drops one without replies', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $withReplies = makeDiscussion(['res_id' => $target, 'title' => 'kept']);
    makeDiscussion(['res_id' => $target, 'parent' => $withReplies->id, 'title' => null]);
    $withReplies->delete();
    makeDiscussion(['res_id' => $target, 'title' => 'gone'])->delete();

    $data = $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}", authHeader($owner))
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['id'])->toBe($withReplies->id)
        ->and($data[0]['deleted_at'])->not->toBeNull()
        ->and($data[0]['title'])->toBeNull()
        ->and($data[0]['content'])->toBeNull()
        ->and($data[0]['abilities'])->toBe(['update' => false, 'delete' => false, 'close' => false, 'reply' => false]);

    // 墓碑的回复照样可读
    $this->getJson("/api/v3/discussions/{$withReplies->id}/replies", authHeader($owner))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('loads a page of topics with a constant number of queries', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $countQueriesFor = function (int $rows) use ($owner, $target) {
        Discussion::where('res_id', $target)->forceDelete();
        foreach (range(1, $rows) as $i) {
            makeDiscussion(['res_id' => $target, 'editor_uid' => makeStudio("n1-{$rows}-{$i}")]);
        }
        $headers = authHeader($owner);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}", $headers)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    // 先热身一次：UserService 是 singleton，查看者自己的摘要只在第一次请求时查库
    $countQueriesFor(1);

    // 正文渲染（MdRender）是逐行的，内容不带模板时不查库；这里守的是编辑者与权限不逐行查
    expect($countQueriesFor(12))->toBe($countQueriesFor(2));
});

/* ------------------------------------------------------------------ 概况 */

it('summarises status counts and creatable types', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    makeDiscussion(['res_id' => $target]);
    makeDiscussion(['res_id' => $target, 'status' => 'close']);
    makeDiscussion(['res_id' => $target, 'status' => 'close']);
    makeDiscussion(['res_id' => $target])->delete();

    $this->getJson("/api/v3/discussions/summary?target_type=sentence&target_id={$target}", authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.status_counts', ['active' => 1, 'close' => 2])
        ->assertJsonPath('data.creatable_types', ['discussion', 'issue', 'glossary', 'note', 'commentary', 'assessment']);   // 句子的管理者能加批注

    $this->getJson("/api/v3/discussions/summary?target_type=sentence&target_id={$target}")
        ->assertOk()
        ->assertJsonPath('data.status_counts', ['active' => 0, 'close' => 0])
        ->assertJsonPath('data.creatable_types', []);
});

/* ------------------------------------------------------------------ 发起话题 */

it('creates a topic', function () {
    $user = makeStudio('author');
    $target = makeTargetSentence(makeStudio('owner'));

    $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence',
        'target_id' => $target,
        'type' => 'issue',
        'title' => '译文漏了一句',
        'content' => '第二句没有译出',
        'severity' => 'error',
        'notify' => false,
    ], authHeader($user))
        ->assertCreated()
        ->assertJsonPath('data.type', 'issue')
        ->assertJsonPath('data.severity', 'error')
        ->assertJsonPath('data.parent_id', null)
        ->assertJsonPath('data.reply_count', 0)
        ->assertJsonPath('data.content_type', 'markdown');

    expect(Discussion::where('editor_uid', $user)->value('res_id'))->toBe($target);
});

it('requires login to create a topic', function () {
    $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence',
        'target_id' => (string) Str::uuid(),
        'title' => 't',
    ])->assertUnauthorized();
});

it('validates severity against the topic type', function () {
    $user = makeStudio('author');
    $target = makeTargetSentence(makeStudio('owner'));
    $payload = ['target_type' => 'sentence', 'target_id' => $target, 'title' => 't', 'notify' => false];

    $this->postJson('/api/v3/discussions', $payload + ['type' => 'discussion', 'severity' => 'error'], authHeader($user))
        ->assertStatus(422)->assertJsonValidationErrors('severity');
    $this->postJson('/api/v3/discussions', $payload + ['type' => 'issue', 'severity' => 'urgent'], authHeader($user))
        ->assertStatus(422)->assertJsonValidationErrors('severity');
    $this->postJson('/api/v3/discussions', $payload + ['type' => 'help', 'severity' => 'urgent'], authHeader($user))
        ->assertStatus(403);   // help 只能发在文章上
});

it('rejects a topic on a target that does not exist', function () {
    $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence',
        'target_id' => (string) Str::uuid(),
        'title' => 't',
        'notify' => false,
    ], authHeader(makeStudio('author')))
        ->assertStatus(422)
        ->assertJsonValidationErrors('target_id');
});

/* ------------------------------------------------------------------ 回复 */

it('replies to a topic, inheriting its type and target', function () {
    $topic = makeDiscussion(['res_id' => makeTargetSentence(makeStudio('owner')), 'type' => 'issue', 'severity' => 'warning']);
    $user = makeStudio('replier');

    $this->postJson("/api/v3/discussions/{$topic->id}/replies", ['content' => '已修正', 'notify' => false], authHeader($user))
        ->assertCreated()
        ->assertJsonPath('data.parent_id', $topic->id)
        ->assertJsonPath('data.type', 'issue')
        ->assertJsonPath('data.target_id', $topic->res_id)
        ->assertJsonMissingPath('data.reply_count');

    expect($topic->fresh()->children_count)->toBe(1);
});

it('lists replies oldest first with the topic type', function () {
    $topic = makeDiscussion(['type' => 'qa']);
    $first = makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id, 'type' => 'discussion', 'created_at' => now()->subHour()]);
    makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id, 'type' => 'qa']);

    $data = $this->getJson("/api/v3/discussions/{$topic->id}/replies")
        ->assertOk()
        ->json('data');

    expect($data)->toHaveCount(2)
        ->and($data[0]['id'])->toBe($first->id)
        ->and(array_column($data, 'type'))->toBe(['qa', 'qa']);   // 历史数据里回复的 type 可能与话题不一致
});

it('refuses to reply to a reply or to a closed topic', function () {
    $user = makeStudio('replier');
    $topic = makeDiscussion();
    $reply = makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id]);
    $closed = makeDiscussion(['status' => 'close']);

    $this->postJson("/api/v3/discussions/{$reply->id}/replies", ['content' => 'x', 'notify' => false], authHeader($user))
        ->assertStatus(422);
    $this->postJson("/api/v3/discussions/{$closed->id}/replies", ['content' => 'x', 'notify' => false], authHeader($user))
        ->assertStatus(409)
        ->assertJsonPath('type', fn ($type) => str_contains($type, 'discussion-closed'));
    $this->postJson("/api/v3/discussions/{$topic->id}/replies", ['notify' => false], authHeader($user))
        ->assertStatus(422);
});

/* ------------------------------------------------------------------ 修改 */

it('patches only the fields that were sent', function () {
    $author = makeStudio('author');
    $topic = makeDiscussion(['editor_uid' => $author, 'title' => 'keep me', 'status' => 'close']);

    $this->patchJson("/api/v3/discussions/{$topic->id}", ['content' => 'new body'], authHeader($author))
        ->assertOk()
        ->assertJsonPath('data.content', 'new body')
        ->assertJsonPath('data.title', 'keep me')
        ->assertJsonPath('data.status', 'close');   // v2 的 PUT 在这里会把话题重新打开
});

it('lets the channel editor update someone else\'s topic', function () {
    $owner = makeStudio('owner');
    $topic = makeDiscussion(['res_id' => makeTargetSentence($owner)]);

    $this->patchJson("/api/v3/discussions/{$topic->id}", ['status' => 'close'], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.status', 'close');
    $this->patchJson("/api/v3/discussions/{$topic->id}", ['content' => 'x'], authHeader(makeStudio('stranger')))
        ->assertForbidden();
});

it('rejects topic-only fields on a reply', function () {
    $author = makeStudio('author');
    $topic = makeDiscussion();
    $reply = makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id, 'editor_uid' => $author]);

    $this->patchJson("/api/v3/discussions/{$reply->id}", ['title' => 't'], authHeader($author))
        ->assertStatus(422)
        ->assertJsonValidationErrors('title');
    $this->patchJson("/api/v3/discussions/{$reply->id}", ['content' => 'fixed'], authHeader($author))
        ->assertOk();
});

it('converts a topic type, cascading to replies and clearing a severity the new type does not take', function () {
    $author = makeStudio('author');
    $topic = makeDiscussion(['editor_uid' => $author, 'type' => 'issue', 'severity' => 'error']);
    $reply = makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id, 'type' => 'issue']);

    $this->patchJson("/api/v3/discussions/{$topic->id}", ['type' => 'discussion'], authHeader($author))
        ->assertOk()
        ->assertJsonPath('data.type', 'discussion')
        ->assertJsonPath('data.severity', null);

    expect($reply->fresh()->type)->toBe('discussion');

    $this->patchJson("/api/v3/discussions/{$topic->id}", ['severity' => 'error'], authHeader($author))
        ->assertStatus(422)
        ->assertJsonValidationErrors('severity');
});

/* ------------------------------------------------------------------ 批量角标 */

it('counts active threads per target in request order, with zeros', function () {
    $owner = makeStudio('owner');
    $a = makeTargetSentence($owner);
    $b = makeTargetSentence($owner);
    $empty = (string) Str::uuid();
    makeDiscussion(['res_id' => $a, 'type' => 'qa', 'editor_uid' => $owner]);
    makeDiscussion(['res_id' => $a, 'type' => 'help']);
    makeDiscussion(['res_id' => $a, 'type' => 'qa', 'status' => 'close']);          // 关闭的不数
    makeDiscussion(['res_id' => $a, 'type' => 'note']);                              // 批注不数
    makeDiscussion(['res_id' => $a, 'type' => 'qa'])->delete();                      // 删除的不数
    $topic = makeDiscussion(['res_id' => $b, 'type' => 'qa']);
    makeDiscussion(['res_id' => $b, 'type' => 'qa', 'parent' => $topic->id]);        // 回复不数

    $this->postJson('/api/v3/discussions/batch-count', ['targets' => [
        ['type' => 'sentence', 'id' => $b],
        ['type' => 'sentence', 'id' => $a],
        ['type' => 'wbw', 'id' => $empty],
    ]], authHeader($owner))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.target_id', $b)
        ->assertJsonPath('data.0.total', 1)
        ->assertJsonPath('data.0.mine', 0)
        ->assertJsonPath('data.1.target_id', $a)
        ->assertJsonPath('data.1.total', 2)
        ->assertJsonPath('data.1.mine', 1)
        ->assertJsonPath('data.2.target_type', 'wbw')
        ->assertJsonPath('data.2.total', 0);
});

it('applies list visibility to batch counts', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $basic = makeBasicUser('basic');
    makeDiscussion(['res_id' => $target, 'type' => 'qa']);
    makeDiscussion(['res_id' => $target, 'type' => 'discussion']);
    makeDiscussion(['res_id' => $target, 'type' => 'issue', 'editor_uid' => $basic]);
    $body = ['targets' => [['type' => 'sentence', 'id' => $target]]];

    makeDiscussion(['res_id' => $target, 'type' => 'discussion', 'editor_uid' => $basic]);

    $this->postJson('/api/v3/discussions/batch-count', $body)->assertJsonPath('data.0.total', 2);                       // 匿名：qa + issue（公开）
    $this->postJson('/api/v3/discussions/batch-count', $body, authHeader($basic))->assertJsonPath('data.0.total', 3);  // basic：再加自己的 discussion
    $this->postJson('/api/v3/discussions/batch-count', $body, authHeader($owner))->assertJsonPath('data.0.total', 4);  // 管理者：全部
});

it('rejects an invalid batch count request', function () {
    $this->postJson('/api/v3/discussions/batch-count', ['targets' => []])->assertStatus(422);
    $this->postJson('/api/v3/discussions/batch-count', ['targets' => [['type' => 'article', 'id' => (string) Str::uuid()]]])
        ->assertStatus(422)->assertJsonValidationErrors('targets.0.type');
    $tooMany = array_fill(0, 501, ['type' => 'sentence', 'id' => (string) Str::uuid()]);
    $this->postJson('/api/v3/discussions/batch-count', ['targets' => $tooMany])->assertStatus(422);
});

it('counts a batch in a fixed number of queries', function () {
    $owner = makeStudio('owner');
    $targets = collect(range(1, 20))->map(function () use ($owner) {
        $id = makeTargetSentence($owner);
        makeDiscussion(['res_id' => $id, 'type' => 'qa']);

        return ['type' => 'sentence', 'id' => $id];
    })->all();
    $this->postJson('/api/v3/discussions/batch-count', ['targets' => [$targets[0]]], authHeader($owner));   // 预热 UserService

    DB::enableQueryLog();
    $this->postJson('/api/v3/discussions/batch-count', ['targets' => $targets], authHeader($owner))->assertOk();
    expect(count(DB::getQueryLog()))->toBeLessThan(5);
});

/* ------------------------------------------------------------------ 批注 */

it('lists annotations only when asked for by type, and counts only threads by default', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    makeDiscussion(['res_id' => $target]);
    $note = makeDiscussion(['res_id' => $target, 'type' => 'commentary', 'title' => null,
        'content' => '{{135-404-50-54}}', 'pos_start' => 3, 'pos_end' => 8, 'quote_exact' => 'dukkh']);
    $base = "/api/v3/discussions?target_type=sentence&target_id={$target}";

    $this->getJson($base, authHeader($owner))->assertJsonCount(1, 'data')->assertJsonPath('data.0.type', 'discussion');
    $this->getJson("{$base}&type=commentary")   // 批注对匿名也可见
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $note->id)
        ->assertJsonPath('data.0.pos_end', 8)
        ->assertJsonPath('data.0.quote_exact', 'dukkh');
    $this->getJson("/api/v3/discussions/summary?target_type=sentence&target_id={$target}", authHeader($owner))
        ->assertJsonPath('data.status_counts.active', 1);
    $this->getJson("/api/v3/discussions/summary?target_type=sentence&target_id={$target}&type=commentary")
        ->assertJsonPath('data.status_counts.active', 1);
});

it('shows an annotation to anyone, without close', function () {
    $owner = makeStudio('owner');
    $note = makeDiscussion(['res_id' => makeTargetSentence($owner), 'type' => 'note', 'editor_uid' => $owner]);

    $this->getJson("/api/v3/discussions/{$note->id}")->assertOk();
    $this->getJson("/api/v3/discussions/{$note->id}", authHeader($owner))
        ->assertJsonPath('data.abilities', ['update' => true, 'delete' => true, 'close' => false, 'reply' => true]);
});

it('lets only the sentence manager create annotations, without notifying', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $stranger = makeStudio('stranger');
    $payload = ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'commentary',
        'content' => '{{135-404-50-54}} {{135-404-55-65}}', 'pos_start' => 0, 'pos_end' => 4, 'quote_exact' => 'sent'];

    $this->getJson("/api/v3/discussions/summary?target_type=sentence&target_id={$target}", authHeader($owner))
        ->assertJsonPath('data.creatable_types', ['discussion', 'issue', 'glossary', 'note', 'commentary', 'assessment']);
    $this->getJson("/api/v3/discussions/summary?target_type=sentence&target_id={$target}", authHeader($stranger))
        ->assertJsonPath('data.creatable_types', ['discussion', 'issue']);

    $this->postJson('/api/v3/discussions', $payload, authHeader($stranger))->assertForbidden();

    Log::spy();
    $this->postJson('/api/v3/discussions', $payload, authHeader($owner))   // 没传 notify：批注本来就不发
        ->assertCreated()
        ->assertJsonPath('data.type', 'commentary')
        ->assertJsonPath('data.title', null)
        ->assertJsonPath('data.pos_end', 4);
    Log::shouldNotHaveReceived('debug', fn (string $message) => $message === 'mq publish');
});

it('validates annotation input', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $post = fn (array $data) => $this->postJson('/api/v3/discussions',
        ['target_type' => 'sentence', 'target_id' => $target, 'notify' => false, ...$data], authHeader($owner));

    $post(['type' => 'commentary', 'content' => '这不是句子模板'])->assertStatus(422)->assertJsonValidationErrors('content');
    $post(['type' => 'note'])->assertStatus(422)->assertJsonValidationErrors('content');
    $post(['type' => 'note', 'content' => 'x', 'target_type' => 'article'])->assertStatus(422)->assertJsonValidationErrors('target_type');
    $post(['type' => 'note', 'content' => 'x', 'pos_start' => 5, 'pos_end' => 2])->assertStatus(422)->assertJsonValidationErrors('pos_end');
    $post(['type' => 'discussion', 'title' => 't', 'pos_start' => 1])->assertStatus(422)->assertJsonValidationErrors('pos_start');
});

it('keeps annotations and threads apart on update', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $note = makeDiscussion(['res_id' => $target, 'type' => 'note', 'title' => null, 'content' => 'gloss']);
    $topic = makeDiscussion(['res_id' => $target]);
    $patch = fn (Discussion $d, array $data) => $this->patchJson("/api/v3/discussions/{$d->id}", $data, authHeader($owner));

    $patch($note, ['status' => 'close'])->assertStatus(422)->assertJsonValidationErrors('status');
    $patch($note, ['type' => 'discussion'])->assertStatus(422)->assertJsonValidationErrors('type');
    $patch($topic, ['type' => 'note'])->assertStatus(422)->assertJsonValidationErrors('type');
    $patch($topic, ['pos_end' => 3])->assertStatus(422)->assertJsonValidationErrors('pos_end');
    $patch($note, ['content' => ''])->assertStatus(422)->assertJsonValidationErrors('content');
    $patch($note, ['type' => 'commentary'])->assertStatus(422)->assertJsonValidationErrors('content');   // 正文不是句子模板

    $patch($note, ['type' => 'commentary', 'content' => '{{1-2-3-4}}', 'pos_end' => 6])
        ->assertOk()
        ->assertJsonPath('data.type', 'commentary')
        ->assertJsonPath('data.pos_end', 6);
});

it('lets other users reply to an annotation, without injecting the reply into the reading page', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $note = makeDiscussion(['res_id' => $target, 'type' => 'note', 'title' => null, 'content' => 'gloss', 'editor_uid' => $owner]);
    $reader = makeStudio('reader');

    $this->getJson("/api/v3/discussions/{$note->id}", authHeader($reader))->assertJsonPath('data.abilities.reply', true);
    $this->postJson("/api/v3/discussions/{$note->id}/replies", ['content' => '请教出处', 'notify' => false], authHeader($reader))
        ->assertCreated()
        ->assertJsonPath('data.type', 'note')
        ->assertJsonPath('data.parent_id', $note->id);

    // 阅读页只注入批注本身：查询口径与 PaliContentService::injectAnnotationNotes 相同
    expect(Discussion::where('res_type', 'sentence')->where('res_id', $target)
        ->whereIn('type', Discussion::INJECTED_TYPES)->whereNull('parent')->pluck('id')->all())
        ->toBe([$note->id]);
});

it('still hides records that are neither threads nor annotations', function () {
    $log = makeDiscussion(['res_type' => 'task', 'type' => 'task-status']);

    $this->getJson("/api/v3/discussions/{$log->id}")->assertNotFound();
});

/* ------------------------------------------------------------------ 译文评估 */

it('lets only the sentence manager assess a sentence, publicly and without notifying', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $payload = ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'assessment',
        'severity' => 'mqm', 'score' => 87, 'content' => '术语一致，语序略生硬'];

    $this->postJson('/api/v3/discussions', $payload)->assertUnauthorized();
    $this->postJson('/api/v3/discussions', $payload, authHeader(makeStudio('stranger')))->assertForbidden();

    Log::spy();
    $id = $this->postJson('/api/v3/discussions', $payload, authHeader($owner))   // 没传 notify：评估本来就不发
        ->assertCreated()
        ->assertJsonPath('data.type', 'assessment')
        ->assertJsonPath('data.severity', 'mqm')
        ->assertJsonPath('data.score', 87)
        ->assertJsonPath('data.title', null)
        ->assertJsonPath('data.content', '术语一致，语序略生硬')
        ->assertJsonPath('data.abilities', ['update' => true, 'delete' => true, 'close' => false, 'reply' => true])
        ->json('data.id');
    Log::shouldNotHaveReceived('debug', fn (string $message) => $message === 'mq publish');

    // 公开：匿名看得到；不带 type 的列表不列评估
    $this->getJson("/api/v3/discussions/{$id}")->assertOk()->assertJsonPath('data.score', 87);
    $base = "/api/v3/discussions?target_type=sentence&target_id={$target}";
    $this->getJson($base)->assertJsonCount(0, 'data');
    $this->getJson("{$base}&type=assessment")->assertJsonCount(1, 'data');
});

it('overwrites the existing assessment of a sentence instead of adding another', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $post = fn (array $data) => $this->postJson('/api/v3/discussions', [
        'target_type' => 'sentence', 'target_id' => $target, 'type' => 'assessment', ...$data,
    ], authHeader($owner));

    $first = $post(['severity' => 'likert', 'score' => 40, 'title' => '初评', 'content' => '漏译'])
        ->assertCreated()->json('data');
    $this->postJson("/api/v3/discussions/{$first['id']}/replies", ['content' => '同意', 'notify' => false], authHeader(makeStudio('reader')))
        ->assertCreated();

    // 重新评估：同一条被覆盖（没传的标题、理由清空），回复保留，返回 200
    $second = $post(['severity' => 'mqm', 'score' => 92])
        ->assertOk()
        ->assertJsonPath('data.id', $first['id'])
        ->assertJsonPath('data.severity', 'mqm')
        ->assertJsonPath('data.score', 92)
        ->assertJsonPath('data.title', null)
        ->assertJsonPath('data.content', null)
        ->assertJsonPath('data.reply_count', 1)
        ->json('data');

    expect($second['created_at'])->toBe($first['created_at'])
        ->and(Discussion::where('res_id', $target)->whereNull('parent')->where('type', 'assessment')->count())->toBe(1);
});

it('validates assessment input', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $post = fn (array $data) => $this->postJson('/api/v3/discussions',
        ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'assessment', 'severity' => 'mqm', 'score' => 80, ...$data],
        authHeader($owner));

    $post(['score' => null])->assertStatus(422)->assertJsonValidationErrors('score');
    $post(['score' => 101])->assertStatus(422)->assertJsonValidationErrors('score');
    $post(['score' => -1])->assertStatus(422)->assertJsonValidationErrors('score');
    $post(['score' => 80.5])->assertStatus(422)->assertJsonValidationErrors('score');   // 只收整数
    $post(['severity' => null])->assertStatus(422)->assertJsonValidationErrors('severity');
    $post(['severity' => 'has space'])->assertStatus(422)->assertJsonValidationErrors('severity');
    $post(['severity' => str_repeat('a', 17)])->assertStatus(422)->assertJsonValidationErrors('severity');
    $post(['target_type' => 'article'])->assertStatus(422)->assertJsonValidationErrors('target_type');
    $post(['pos_start' => 0, 'pos_end' => 2])->assertStatus(422)->assertJsonValidationErrors(['pos_start', 'pos_end']);
    // 其它类型不收分数
    $this->postJson('/api/v3/discussions', ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'issue',
        'title' => 't', 'score' => 50, 'notify' => false], authHeader($owner))
        ->assertStatus(422)->assertJsonValidationErrors('score');
});

it('updates an assessment score and method, and keeps it apart from other types', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $assessment = makeDiscussion(['res_id' => $target, 'type' => 'assessment', 'title' => null,
        'severity' => 'mqm', 'score' => 50]);
    $topic = makeDiscussion(['res_id' => $target, 'type' => 'issue']);
    $patch = fn (Discussion $d, array $data) => $this->patchJson("/api/v3/discussions/{$d->id}", $data, authHeader($owner));

    $patch($assessment, ['score' => 120])->assertStatus(422)->assertJsonValidationErrors('score');
    $patch($assessment, ['severity' => null])->assertStatus(422)->assertJsonValidationErrors('severity');
    $patch($assessment, ['status' => 'close'])->assertStatus(422)->assertJsonValidationErrors('status');
    $patch($assessment, ['type' => 'note'])->assertStatus(422)->assertJsonValidationErrors('type');
    $patch($topic, ['type' => 'assessment'])->assertStatus(422)->assertJsonValidationErrors('type');
    $patch($topic, ['score' => 10])->assertStatus(422)->assertJsonValidationErrors('score');

    $patch($assessment, ['score' => 66, 'severity' => 'llm-judge', 'content' => '复核'])
        ->assertOk()
        ->assertJsonPath('data.score', 66)
        ->assertJsonPath('data.severity', 'llm-judge')
        ->assertJsonPath('data.content', '复核');
});

/* ------------------------------------------------------------------ 外挂术语与锚点 */

it('lets only the sentence manager create a glossary topic, with the headword as title and an anchor', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $payload = ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'glossary', 'title' => 'dukkha',
        'content' => '这里译作「苦」不妥', 'pos_start' => 0, 'pos_end' => 6, 'quote_exact' => 'dukkha', 'notify' => false];

    $this->postJson('/api/v3/discussions', $payload, authHeader(makeStudio('stranger')))->assertForbidden();

    $id = $this->postJson('/api/v3/discussions', $payload, authHeader($owner))
        ->assertCreated()
        ->assertJsonPath('data.type', 'glossary')
        ->assertJsonPath('data.title', 'dukkha')
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.pos_end', 6)
        ->assertJsonPath('data.quote_exact', 'dukkha')
        ->json('data.id');

    // 公开可见，算作评论线程（不带 type 的列表与角标都数它）
    $this->getJson("/api/v3/discussions/{$id}")->assertOk();
    $this->getJson("/api/v3/discussions?target_type=sentence&target_id={$target}")
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id);
    $this->postJson('/api/v3/discussions/batch-count', ['targets' => [['type' => 'sentence', 'id' => $target]]])
        ->assertJsonPath('data.0.total', 1);
});

it('validates glossary input', function () {
    $owner = makeStudio('owner');
    $target = makeTargetSentence($owner);
    $post = fn (array $data) => $this->postJson('/api/v3/discussions',
        ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'glossary', 'notify' => false, ...$data], authHeader($owner));

    $post(['content' => 'x'])->assertStatus(422)->assertJsonValidationErrors('title');
    $post(['title' => 'dukkha', 'target_type' => 'article'])->assertStatus(422)->assertJsonValidationErrors('target_type');
    $post(['title' => 'dukkha', 'severity' => 'error'])->assertStatus(422)->assertJsonValidationErrors('severity');
});

it('accepts an anchor on issue topics', function () {
    $target = makeTargetSentence(makeStudio('owner'));
    $author = makeStudio('author');

    $id = $this->postJson('/api/v3/discussions', ['target_type' => 'sentence', 'target_id' => $target, 'type' => 'issue',
        'title' => '漏译', 'pos_start' => 2, 'pos_end' => 5, 'quote_exact' => 'nte', 'notify' => false], authHeader($author))
        ->assertCreated()
        ->assertJsonPath('data.pos_start', 2)
        ->json('data.id');

    $this->patchJson("/api/v3/discussions/{$id}", ['pos_end' => 7], authHeader($author))
        ->assertOk()->assertJsonPath('data.pos_end', 7);
});

it('keeps the anchor across type changes, and guards conversion to glossary', function () {
    $owner = makeStudio('owner');
    $author = makeStudio('author');
    $target = makeTargetSentence($owner);
    $issue = makeDiscussion(['res_id' => $target, 'type' => 'issue', 'editor_uid' => $author, 'pos_start' => 1, 'pos_end' => 3]);
    $onWbw = makeDiscussion(['res_type' => 'wbw', 'editor_uid' => $owner]);

    $this->patchJson("/api/v3/discussions/{$issue->id}", ['type' => 'glossary'], authHeader($author))->assertForbidden();
    $this->patchJson("/api/v3/discussions/{$onWbw->id}", ['type' => 'glossary'], authHeader($owner))
        ->assertStatus(422)->assertJsonValidationErrors('type');
    $this->patchJson("/api/v3/discussions/{$issue->id}", ['type' => 'discussion', 'pos_end' => 4], authHeader($author))
        ->assertStatus(422)->assertJsonValidationErrors('pos_end');

    $this->patchJson("/api/v3/discussions/{$issue->id}", ['type' => 'glossary'], authHeader($owner))
        ->assertOk()->assertJsonPath('data.type', 'glossary')->assertJsonPath('data.pos_end', 3);
    // 改成不收锚点的类型：锚点留着（不显示、不影响校验），改回来位置还在
    $this->patchJson("/api/v3/discussions/{$issue->id}", ['type' => 'discussion'], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.pos_end', 3);
    $this->patchJson("/api/v3/discussions/{$issue->id}", ['content' => '改正文'], authHeader($owner))->assertOk();
    $this->patchJson("/api/v3/discussions/{$issue->id}", ['type' => 'issue'], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.pos_start', 1)
        ->assertJsonPath('data.pos_end', 3);
});

/* ------------------------------------------------------------------ 删除 */

it('soft deletes a topic for its author and returns the tombstone', function () {
    $author = makeStudio('author');
    $topic = makeDiscussion(['editor_uid' => $author]);

    $this->deleteJson("/api/v3/discussions/{$topic->id}", [], authHeader($author))
        ->assertOk()
        ->assertJsonPath('data.id', $topic->id)
        ->assertJsonPath('data.content', null)
        ->assertJsonPath('data.deleted_at', fn ($value) => $value !== null);

    $this->assertSoftDeleted($topic);
    // 没有回复的墓碑不再可读
    $this->getJson("/api/v3/discussions/{$topic->id}")->assertNotFound();
});

it('decrements the topic reply count when a reply is deleted', function () {
    $author = makeStudio('author');
    $topic = makeDiscussion(['children_count' => 1]);
    $reply = makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id, 'editor_uid' => $author]);

    $this->deleteJson("/api/v3/discussions/{$reply->id}", [], authHeader($author))->assertOk();

    $this->assertSoftDeleted($reply);
    expect($topic->fresh()->children_count)->toBe(0);
    $this->getJson("/api/v3/discussions/{$topic->id}", authHeader($author))->assertJsonPath('data.reply_count', 0);
});

it('lets the target manager delete someone else\'s reply and topic', function () {
    $owner = makeStudio('owner');
    $topic = makeDiscussion(['res_id' => makeTargetSentence($owner)]);
    $reply = makeDiscussion(['parent' => $topic->id, 'res_id' => $topic->res_id]);

    $this->getJson("/api/v3/discussions/{$reply->id}", authHeader($owner))
        ->assertJsonPath('data.abilities.delete', true);
    $this->deleteJson("/api/v3/discussions/{$reply->id}", [], authHeader($owner))->assertOk();
    $this->deleteJson("/api/v3/discussions/{$topic->id}", [], authHeader($owner))->assertOk();
    $this->assertSoftDeleted($reply);
    $this->assertSoftDeleted($topic);
});

it('forbids deleting for users who are neither author nor manager', function () {
    $topic = makeDiscussion(['res_id' => makeTargetSentence(makeStudio('owner'))]);

    $this->getJson("/api/v3/discussions/{$topic->id}", authHeader($stranger = makeStudio('stranger')))
        ->assertJsonPath('data.abilities.delete', false);
    $this->deleteJson("/api/v3/discussions/{$topic->id}", [], authHeader($stranger))->assertForbidden();
    $this->deleteJson("/api/v3/discussions/{$topic->id}")->assertUnauthorized();
});

/* ------------------------------------------------------------------ v2 快照 */

it('keeps the v2 delete response while soft deleting underneath', function () {
    $author = makeStudio('author');
    $topic = makeDiscussion(['editor_uid' => $author]);

    $this->deleteJson("/api/v2/discussion/{$topic->id}", [], authHeader($author))
        ->assertOk()
        ->assertJsonPath('ok', true);

    $this->assertSoftDeleted($topic);
});
