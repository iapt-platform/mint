<?php

use App\Models\Discussion;
use App\Models\PaliText;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * v3 tipitaka-discussions：按经文范围（channel + 书 / 章 / 段落区间）列讨论。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 *
 *   GET /api/v3/tipitaka-discussions/{channel}?book=&chapter=|para=&to=&type=&status=&severity=
 */

/** book 9301 的一章：起始段 1，长 3 段（1–3）；第 4 段在章外 */
function makeDiscussionChapter(): void
{
    foreach ([1 => 3, 2 => null, 3 => null, 4 => null] as $para => $chapterLen) {
        (new PaliText)->forceFill([
            'book' => 9301, 'paragraph' => $para, 'level' => $chapterLen ? 2 : 100,
            'class' => '', 'toc' => '', 'text' => '', 'html' => '', 'lenght' => 10,
            'chapter_len' => $chapterLen, 'pcd_book_id' => 0, 'uid' => (string) Str::uuid(),
        ])->save();
    }
}

function makeTextDiscussion(string $sentenceUid, array $attrs = []): Discussion
{
    return Discussion::forceCreate(array_merge([
        'res_type' => 'sentence',
        'res_id' => $sentenceUid,
        'type' => 'issue',
        'title' => 't',
        'content' => 'body',
        'editor_uid' => makeStudio('td-'.Str::random(8)),
    ], $attrs));
}

function tipitakaDiscussionsUrl(string $channel, string $query = ''): string
{
    return "/api/v3/tipitaka-discussions/{$channel}".($query === '' ? '' : "?{$query}");
}

it('lists topics in a chapter in text order, with sentence coordinates', function () {
    makeDiscussionChapter();
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $p3 = makeSentence($channel, 9301, 3, 1, 'third');
    $p1b = makeSentence($channel, 9301, 1, 9, 'first, later sentence');
    $p1a = makeSentence($channel, 9301, 1, 2, 'first');
    $outside = makeSentence($channel, 9301, 4, 1, 'outside the chapter');
    $c = makeTextDiscussion($p3->uid, ['title' => 'c']);
    $b = makeTextDiscussion($p1b->uid, ['title' => 'b']);
    $a = makeTextDiscussion($p1a->uid, ['title' => 'a', 'type' => 'glossary']);
    makeTextDiscussion($outside->uid);
    makeTextDiscussion($p1a->uid, ['parent' => $a->id]);   // 回复不列

    $json = $this->getJson(tipitakaDiscussionsUrl($channel, 'book=9301&chapter=1'))
        ->assertOk()
        ->json();

    expect(array_column($json['data'], 'id'))->toBe([$a->id, $b->id, $c->id])
        ->and($json['data'][0]['sentence'])->toBe(['book' => 9301, 'paragraph' => 1, 'word_start' => 2, 'word_end' => 2])
        ->and($json['data'][0])->toHaveKeys(['id', 'type', 'title', 'status', 'editor', 'abilities', 'reply_count', 'pos_start'])
        ->and($json['data'][0]['reply_count'])->toBe(1)
        ->and($json['meta']['total'])->toBe(3);
});

it('narrows by paragraph range and by the whole book or channel', function () {
    $channel = makeChannel(makeStudio('owner'));
    foreach ([1, 2, 3] as $para) {
        makeTextDiscussion(makeSentence($channel, 9302, $para, 1, "p{$para}")->uid, ['title' => "p{$para}"]);
    }
    makeTextDiscussion(makeSentence($channel, 9303, 1, 1, 'other book')->uid, ['title' => 'other book']);
    $titles = fn (string $query) => array_column($this->getJson(tipitakaDiscussionsUrl($channel, $query))->assertOk()->json('data'), 'title');

    expect($titles('book=9302&para=2&to=3'))->toBe(['p2', 'p3'])
        ->and($titles('book=9302&para=2'))->toBe(['p2'])
        ->and($titles('book=9302'))->toBe(['p1', 'p2', 'p3'])
        ->and($titles(''))->toBe(['p1', 'p2', 'p3', 'other book']);
});

it('only lists topics on sentences of this channel', function () {
    $owner = makeStudio('owner');
    $mine = makeChannel($owner, 'mine');
    $other = makeChannel($owner, 'other');
    makeTextDiscussion(makeSentence($mine, 9304, 1, 1, 'mine')->uid, ['title' => 'mine']);
    makeTextDiscussion(makeSentence($other, 9304, 1, 1, 'other')->uid, ['title' => 'other']);

    $this->getJson(tipitakaDiscussionsUrl($mine, 'book=9304'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'mine');
    $this->getJson(tipitakaDiscussionsUrl((string) Str::uuid()))->assertOk()->assertJsonCount(0, 'data');
});

it('lists only threads by default, and filters by several types, status and severity', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $sent = makeSentence($channel, 9305, 1, 1, 'text')->uid;
    makeTextDiscussion($sent, ['type' => 'issue', 'severity' => 'error', 'title' => 'issue-error']);
    makeTextDiscussion($sent, ['type' => 'issue', 'severity' => 'warning', 'status' => 'close', 'title' => 'issue-closed']);
    makeTextDiscussion($sent, ['type' => 'glossary', 'title' => 'glossary']);
    makeTextDiscussion($sent, ['type' => 'discussion', 'title' => 'discussion']);
    makeTextDiscussion($sent, ['type' => 'note', 'title' => 'note']);
    $titles = fn (string $query) => array_column(
        $this->getJson(tipitakaDiscussionsUrl($channel, "book=9305&{$query}"), authHeader($owner))->assertOk()->json('data'), 'title');

    expect($titles(''))->toEqualCanonicalizing(['issue-error', 'issue-closed', 'glossary', 'discussion'])   // 批注不在默认里
        ->and($titles('type=issue,glossary'))->toEqualCanonicalizing(['issue-error', 'issue-closed', 'glossary'])
        ->and($titles('type=note'))->toBe(['note'])
        ->and($titles('type=issue&status=active'))->toBe(['issue-error'])
        ->and($titles('type=issue&severity=warning'))->toBe(['issue-closed']);
});

it('keeps tombstones of deleted topics that still have replies', function () {
    $channel = makeChannel(makeStudio('owner'));
    $sent = makeSentence($channel, 9306, 1, 1, 'text')->uid;
    $withReply = makeTextDiscussion($sent, ['title' => 'tombstone']);
    makeTextDiscussion($sent, ['parent' => $withReply->id]);
    $withReply->delete();
    makeTextDiscussion($sent, ['title' => 'gone'])->delete();

    $this->getJson(tipitakaDiscussionsUrl($channel, 'book=9306'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $withReply->id)
        ->assertJsonPath('data.0.title', null);
});

it('applies the same visibility as the per-target list', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $sent = makeSentence($channel, 9307, 1, 1, 'text')->uid;
    $basic = makeStudio('basic');
    UserInfo::where('userid', $basic)->update(['role' => json_encode(['basic'])]);
    makeTextDiscussion($sent, ['type' => 'discussion', 'title' => 'private-others']);
    makeTextDiscussion($sent, ['type' => 'discussion', 'title' => 'private-mine', 'editor_uid' => $basic]);
    makeTextDiscussion($sent, ['type' => 'issue', 'title' => 'issue']);
    $titles = fn (array $headers) => array_column(
        $this->getJson(tipitakaDiscussionsUrl($channel, 'book=9307'), $headers)->assertOk()->json('data'), 'title');

    expect($titles([]))->toBe(['issue'])                                                     // 匿名：看不到 discussion
        ->and($titles(authHeader($basic)))->toEqualCanonicalizing(['private-mine', 'issue'])  // basic：只看得到自己的 discussion
        ->and($titles(authHeader($owner)))->toHaveCount(3);                                   // channel 编辑者：全部
});

it('rejects invalid parameters', function () {
    makeDiscussionChapter();
    $channel = makeChannel(makeStudio('owner'));
    $get = fn (string $query) => $this->getJson(tipitakaDiscussionsUrl($channel, $query));

    $get('chapter=1')->assertStatus(422)->assertJsonValidationErrors('book');
    $get('book=9301&chapter=1&para=2')->assertStatus(422)->assertJsonValidationErrors('chapter');
    $get('book=9301&para=3&to=2')->assertStatus(422)->assertJsonValidationErrors('to');
    $get('type=issue,bogus')->assertStatus(422)->assertJsonValidationErrors('type');
    $get('per_page=500')->assertStatus(422)->assertJsonValidationErrors('per_page');
    $get('book=9301&chapter=99')->assertNotFound();               // 没有这一段（同 tipitaka-reading）
    $this->getJson('/api/v3/tipitaka-discussions/not-a-uuid')->assertNotFound();
});

it('does not run a query per row', function () {
    $channel = makeChannel(makeStudio('owner'));
    $countQueriesFor = function (int $rows) use ($channel) {
        $book = 9400 + $rows;
        foreach (range(1, $rows) as $i) {
            makeTextDiscussion(makeSentence($channel, $book, $i, 1, "s{$i}")->uid, ['editor_uid' => makeStudio("n-{$book}-{$i}")]);
        }
        $this->getJson(tipitakaDiscussionsUrl($channel, "book={$book}"))->assertOk();   // 预热 UserService 等缓存
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson(tipitakaDiscussionsUrl($channel, "book={$book}"))->assertOk()->assertJsonCount($rows, 'data');
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($countQueriesFor(12))->toBe($countQueriesFor(2));
});
