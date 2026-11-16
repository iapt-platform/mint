<?php

use App\Models\PaliText;
use App\Services\OpenSearchService;
use App\Services\PaliContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * opensearch:index-tipitaka --granularity=chapter：每个「章节 × channel」建一份文档。
 *
 * 回归：求一本书最后一章的结束段时曾用 `->first()->value('paragraph')`，模型实例上的 value()
 * 新开不带条件的查询、拿到全表第一行的段号，最后一章的范围为空，从不被索引。
 * OpenSearch 与取文都换成假的，只看建了哪些文档。
 */

function itcParagraph(int $book, int $para, int $level, string $toc = ''): void
{
    (new PaliText)->forceFill([
        'book' => $book,
        'paragraph' => $para,
        'level' => $level,
        'class' => '',
        'toc' => $toc,
        'text' => $toc,
        'html' => '',
        'pcd_book_id' => 0,
        'chapter_len' => 1,
        'chapter_strlen' => 10,
        'uid' => (string) Str::uuid(),
    ])->save();
}

it('indexes every chapter of a book, including the last one', function () {
    // 先放一本段号更小的书：修复前 value() 拿到的「全表第一行」就是它，最后一章的范围变空
    itcParagraph(1, 1, 1, 'other book');

    itcParagraph(9300, 3, 1, 'Book');
    itcParagraph(9300, 4, 2, '1. First');
    itcParagraph(9300, 5, 100);
    itcParagraph(9300, 6, 2, '2. Last');
    itcParagraph(9300, 7, 100);
    itcParagraph(9300, 8, 100);

    $channel = makeChannel((string) Str::uuid(), 'pali');
    foreach ([4, 5, 6, 7, 8] as $para) {
        makeSentence($channel, 9300, $para, 1, "para {$para}");
    }

    $this->mock(PaliContentService::class, function ($mock) {
        $mock->shouldReceive('readParagraph')->andReturnUsing(fn ($book, $para) => [
            'display' => "<p>{$para}</p>",
            'sentences' => [['html' => "title {$para}"]],
        ]);
    });
    $created = [];
    $this->mock(OpenSearchService::class, function ($mock) use (&$created) {
        $mock->shouldReceive('testConnection')->andReturn([true, 'ok']);
        $mock->shouldReceive('create')->andReturnUsing(function (string $id, array $body) use (&$created) {
            $created[$id] = $body;

            return [];
        });
    });

    $this->artisan('opensearch:index-tipitaka', ['book' => 9300, '--granularity' => 'chapter', '--summary' => 'off'])
        ->assertExitCode(0);

    // 书名那一段（9300:3）到下一个标题之间没有句子，不建文档
    expect(array_keys($created))->toBe([
        "tipitaka_chapter_9300-4_{$channel}",
        "tipitaka_chapter_9300-6_{$channel}",
    ])->and($created["tipitaka_chapter_9300-6_{$channel}"]['content']['text']['zh'])->toBe('678');
});
