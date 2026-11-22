<?php

use App\Facades\RedisCache;
use App\Models\Discussion;
use App\Models\PaliText;
use App\Services\PaliContentService;
use App\Services\ReadingIndexService;
use App\Services\V3\PaliTextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 阅读缓存（api-v13/documents/reading-cache-plan.html）：
 * L0 pali_texts 按书缓存、A1 整块批量渲染、L1 段落成品、L2 段号 / 书号索引。
 */
function makePaliRow(int $book, int $para, int $level, int $chapterLen = 0, string $class = 'bodytext', string $toc = ''): void
{
    (new PaliText)->forceFill([
        'book' => $book,
        'paragraph' => $para,
        'level' => $level,
        'class' => $class,
        'toc' => $toc,
        'text' => '',
        'html' => '',
        'lenght' => 10 * $para,
        'chapter_len' => $chapterLen,
        'chapter_strlen' => 100,
        'parent' => 0,
        'pcd_book_id' => 0,
        'uid' => (string) Str::uuid(),
    ])->save();
}

function makeReadingNote(string $sentenceUid, string $type, string $content, ?int $score = null): Discussion
{
    $discussion = new Discussion;
    $discussion->forceFill([
        'res_id' => $sentenceUid,
        'res_type' => 'sentence',
        'type' => $type,
        'title' => $content,
        'content' => $content,
        'content_type' => 'markdown',
        'editor_uid' => makeStudio('note-'.Str::random(6)),
        'score' => $score,
        'pos_start' => 0,
        'pos_end' => 1,
    ])->save();

    return $discussion;
}

/**
 * 计数这段代码查了几次库。
 */
function countQueries(Closure $callback): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $callback();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

it('caches pali_texts per book and serves headings, levels and classes without queries', function () {
    makePaliRow(9301, 1, 1, 5, 'book', 'Book');
    makePaliRow(9301, 2, 2, 4, 'chapter', 'Chapter');
    makePaliRow(9301, 3, 100, 0, 'gatha1');

    app(PaliTextService::class)->headings(9301);
    app(PaliTextService::class)->paragraphs(9301, 1, 3);

    // 新实例（没有进程内记忆）也只读缓存
    $service = new PaliTextService;
    expect(countQueries(function () use ($service) {
        expect($service->headingLevel(9301, 2))->toBe(2)
            ->and($service->headingLevel(9301, 3))->toBe(0)
            ->and($service->paragraph(9301, 3))->toBe(['level' => 100, 'class' => 'gatha1', 'length' => 30])
            ->and($service->chapterRange(9301, 2))->toBe([2, 5])
            ->and($service->chapterRange(9301, 3))->toBe([3, 3])
            ->and($service->bookStart(9301, 3))->toMatchArray(['para' => 1, 'toc' => 'Book']);
    }))->toBe(0);

    expect($service->chapterRange(9301, 99))->toBeNull();
});

it('renders a block exactly like rendering each paragraph alone', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    makePaliRow(9302, 1, 2, 3, 'chapter');
    foreach ([1, 2, 3] as $para) {
        $sentence = makeSentence($channel, 9302, $para, 1, "first of {$para}");
        makeSentence($channel, 9302, $para, 2, "second of {$para}");
        makeReadingNote($sentence->uid, 'note', "note of {$para}");
    }
    // 只有第 2 段有评估：第 2 段的另一句补灰色对勾，别的段不补（按段算，不是按整块）
    $assessed = makeSentence($channel, 9302, 2, 3, 'assessed');
    makeReadingNote($assessed->uid, 'assessment', 'ok', 90);

    $service = app(PaliContentService::class);
    $block = $service->readParagraphs(9302, [1, 2, 3], $channel);

    foreach ([1, 2, 3] as $para) {
        PaliContentService::forgetParagraph(9302, $para, $channel);
    }
    foreach ([1, 2, 3] as $para) {
        expect($service->readParagraph(9302, $para, $channel))->toBe($block[$para]);
    }
    expect($block[1]['display'])->toContain('<h2>')->toContain('note of 1')->toContain('anno-footnotes')
        ->and($block[2]['display'])->toContain('anno-assessment')
        ->and($block[1]['display'])->not->toContain('anno-assessment')
        ->and($block[3]['display'])->not->toContain('anno-assessment');
});

it('renders a block with a fixed number of queries however many paragraphs it has', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    $render = function (int $book, int $count) use ($channel) {
        foreach (range(1, $count) as $para) {
            $sentence = makeSentence($channel, $book, $para, 1, "text {$para}");
            makeReadingNote($sentence->uid, 'note', "note {$para}");
        }
        app(PaliTextService::class)->headings($book);
        app(PaliTextService::class)->paragraphs($book, 1, $count);

        return countQueries(fn () => app(PaliContentService::class)->readParagraphs($book, range(1, $count), $channel));
    };

    expect($render(9303, 20))->toBe($render(9304, 2));
});

it('serves cached paragraphs without touching the database', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    makeSentence($channel, 9305, 1, 1, 'cached');
    $url = "/api/v3/tipitaka-reading/{$channel}?book=9305";
    $first = $this->getJson($url)->assertOk()->json();

    // 鉴权中间件等外围查询之外，正文、候选、进度都不查库
    $queries = countQueries(fn () => expect($this->getJson($url)->assertOk()->json())->toBe($first));
    $baseline = countQueries(fn () => $this->getJson("/api/v3/tipitaka-reading/{$channel}?book=9305&para=999")->assertOk());
    expect($queries)->toBe($baseline);
});

it('refreshes the paragraph index only when the set of translated paragraphs changes', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    $first = makeSentence($channel, 9306, 1, 1, 'one');
    $index = fn () => new ReadingIndexService;
    expect($index()->paragraphs($channel, 9306))->toBe([1]);

    $sentence = makeSentence($channel, 9306, 3, 1, 'three');
    expect($index()->paragraphs($channel, 9306))->toBe([1, 3]);

    // 只改内容：段落成品删掉，索引不动（不重建）
    app(PaliContentService::class)->readParagraph(9306, 1, $channel);
    $first->forceFill(['content' => 'one again', 'strlen' => 9])->save();
    expect(RedisCache::has(PaliContentService::paragraphCacheKey(9306, 1, $channel, 'html')))->toBeFalse()
        ->and(countQueries(fn () => expect($index()->paragraphs($channel, 9306))->toBe([1, 3])))->toBe(0);

    // strlen 变成 0 就不算有译文
    $sentence->forceFill(['content' => '', 'strlen' => 0])->save();
    expect($index()->paragraphs($channel, 9306))->toBe([1]);

    // 批注不碰索引
    makeReadingNote($first->uid, 'note', 'n');
    expect(countQueries(fn () => $index()->paragraphs($channel, 9306)))->toBe(0);
});

it('keeps the book index in step with books gaining or losing translations', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    makeSentence($channel, 9307, 1, 1, 'a');
    expect((new ReadingIndexService)->books($channel))->toBe([9307]);

    $other = makeSentence($channel, 9308, 1, 1, 'b');
    expect((new ReadingIndexService)->books($channel))->toBe([9307, 9308]);

    // 搬去别的书：旧书少了、新书多了
    $other->forceFill(['book_id' => 9309])->save();
    expect((new ReadingIndexService)->books($channel))->toBe([9307, 9309]);

    $other->delete();
    expect((new ReadingIndexService)->books($channel))->toBe([9307]);
});

it('walks a whole channel across books with the cursor', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    makeSentence($channel, 9310, 1, 1, 'a');
    makeSentence($channel, 9310, 2, 1, 'b');
    makeSentence($channel, 9311, 5, 1, 'c');

    $page = $this->getJson("/api/v3/tipitaka-reading/{$channel}?page_size=2")->assertOk();
    expect(array_column($page->json('data'), 'para'))->toBe([1, 2])
        ->and($page->json('meta.next_cursor'))->toBe('9310-2');

    $page = $this->getJson("/api/v3/tipitaka-reading/{$channel}?page_size=2&after=9310-2")->assertOk();
    expect($page->json('data.0'))->toMatchArray(['book' => 9311, 'para' => 5])
        ->and($page->json('meta.next_cursor'))->toBeNull();
});

it('clears the paragraph cache only after the transaction commits', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    $sentence = makeSentence($channel, 9313, 1, 1, 'before');
    app(PaliContentService::class)->readParagraph(9313, 1, $channel);
    $key = PaliContentService::paragraphCacheKey(9313, 1, $channel, 'html');

    DB::transaction(function () use ($sentence, $key) {
        $sentence->forceFill(['content' => 'after'])->save();
        expect(RedisCache::has($key))->toBeTrue();
    });
    expect(RedisCache::has($key))->toBeFalse();
});

it('splits a book into flat chapters, each running up to the next heading', function () {
    makePaliRow(9320, 1, 1, 10);
    makePaliRow(9320, 2, 2, 5);
    makePaliRow(9320, 4, 3, 3);
    makePaliRow(9320, 7, 2, 4);

    $service = app(PaliTextService::class);
    expect($service->flatChapters(9320))->toBe([1 => 1, 2 => 3, 4 => 6, 7 => 10])
        ->and($service->flatChapterOf(9320, 5))->toBe(4)
        ->and($service->flatChapterOf(9320, 10))->toBe(7)
        ->and($service->flatChapterOf(9320, 11))->toBeNull();
});

it('caches the reader chapters and drops only the chapter whose sentence changed', function () {
    $channel = makeChannel(makeStudio('cache-owner'), 'cache channel');
    makePaliRow(9321, 1, 2, 2);
    makePaliRow(9321, 3, 2, 2);
    makeSentence($channel, 9321, 1, 1, 'chapter one');
    $changed = makeSentence($channel, 9321, 4, 1, 'chapter two');

    $service = app(PaliContentService::class);
    $display = $service->readChapters(9321, 1, 4, $channel);
    expect($display)->toContain('chapter one')->toContain('chapter two')
        ->and($display)->toBe(implode('', array_column($service->readParagraphs(9321, [1, 2, 3, 4], $channel), 'display')))
        ->and(countQueries(fn () => $service->readChapters(9321, 1, 4, $channel)))->toBe(0);

    $changed->forceFill(['content' => 'chapter two edited'])->save();
    expect(RedisCache::has(PaliContentService::chapterCacheKey(9321, 1, $channel, 'html')))->toBeTrue()
        ->and(RedisCache::has(PaliContentService::chapterCacheKey(9321, 3, $channel, 'html')))->toBeFalse()
        ->and($service->readChapters(9321, 1, 4, $channel))->toContain('chapter two edited');
});
