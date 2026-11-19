<?php

use App\Models\BookTitle;
use App\Models\PageNumber;
use App\Models\PaliText;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\WbwTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * GET /v3/tipitaka-page-numbers/{page}：按印本页码 / CS 段落号找段落（取代 v2 search-page-number）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function pageMark(string $type, int $volume, int $page, int $book, int $para, int $bookId): void
{
    PageNumber::forceCreate([
        'type' => $type, 'volume' => $volume, 'page' => $page, 'book' => $book,
        'paragraph' => $para, 'wid' => 1, 'pcd_book_id' => $bookId,
    ]);
}

/**
 * @param  list<string>  $tags
 */
function pageBook(int $bookId, int $book, string $toc, array $tags = []): void
{
    BookTitle::forceCreate(['sn' => $bookId, 'book' => $book, 'paragraph' => 3, 'title' => $toc]);
    $head = PaliText::forceCreate([
        'book' => $book, 'paragraph' => 3, 'level' => 1, 'class' => '', 'toc' => $toc,
        'text' => '', 'html' => '', 'uid' => (string) Str::uuid(),
    ]);
    foreach ($tags as $name) {
        $tag = Tag::forceCreate(['name' => $name, 'owner_id' => (string) Str::uuid()]);
        TagMap::forceCreate(['table_name' => 'pali_texts', 'anchor_id' => $head->uid, 'tag_id' => $tag->id]);
    }
}

it('finds a page across editions plus CS paragraph numbers, ordered by edition', function () {
    // book_id 100 在静态书名表里：PTS 书名 dīghanikāya
    pageBook(100, 94, '(DN) Mahāvaggapāḷi', ['sutta', 'mūla']);
    pageMark('M', 0, 100, 94, 50, 100);
    pageMark('P', 2, 100, 94, 40, 100);
    pageMark('P', 2, 101, 94, 41, 100);       // 别的页
    WbwTemplate::forceCreate([
        'book' => 94, 'paragraph' => 60, 'wid' => 1, 'word' => 'para100', 'real' => 'para100', 'type' => '',
        'gramma' => '', 'part' => '', 'style' => '', 'pcd_book_id' => 100, 'weight' => 1,
    ]);

    $this->getJson('/api/v3/tipitaka-page-numbers/100')
        ->assertOk()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('data.*.edition', ['PTS', 'My', 'CS'])
        ->assertJsonPath('data.0', [
            'edition' => 'PTS', 'volume' => 2, 'page' => 100, 'book' => 94, 'para' => 40, 'book_id' => 100,
            'book_title' => '(DN) Mahāvaggapāḷi', 'book_para' => 3, 'edition_title' => 'dīghanikāya', 'layer' => 'mula',
            'tags' => ['sutta', 'mūla'],
        ])
        ->assertJsonPath('data.1.volume', null)         // 卷号 0 = 未知
        ->assertJsonPath('data.2.edition_title', null);
});

it('filters by edition and volume; a volume excludes CS paragraph numbers', function () {
    pageMark('P', 2, 100, 94, 40, 100);
    pageMark('P', 3, 100, 95, 40, 101);
    pageMark('M', 2, 100, 94, 50, 100);
    WbwTemplate::forceCreate([
        'book' => 94, 'paragraph' => 60, 'wid' => 1, 'word' => 'para100', 'real' => 'para100', 'type' => '',
        'gramma' => '', 'part' => '', 'style' => '', 'pcd_book_id' => 100, 'weight' => 1,
    ]);

    $this->getJson('/api/v3/tipitaka-page-numbers/100?edition=PTS')->assertJsonPath('data.*.volume', [2, 3]);
    $this->getJson('/api/v3/tipitaka-page-numbers/100?volume=2')->assertJsonPath('data.*.edition', ['PTS', 'My']);
    $this->getJson('/api/v3/tipitaka-page-numbers/100?edition=CS')->assertJsonPath('data.*.para', [60]);
});

it('rejects invalid filters and non-numeric pages', function () {
    $this->getJson('/api/v3/tipitaka-page-numbers/100?edition=P')->assertStatus(422)->assertJsonValidationErrors('edition');
    $this->getJson('/api/v3/tipitaka-page-numbers/100?volume=0')->assertStatus(422)->assertJsonValidationErrors('volume');
    $this->getJson('/api/v3/tipitaka-page-numbers/abc')->assertNotFound();
});

it('does not query per row', function () {
    $queries = function (int $rows): int {
        PageNumber::query()->delete();
        for ($i = 1; $i <= $rows; $i++) {
            pageMark('P', 1, 100, 1, $i, $i);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v3/tipitaka-page-numbers/100')->assertOk()->assertJsonCount($rows, 'data');

        return count(DB::getQueryLog());
    };

    expect($queries(2))->toBe($queries(12));
});
