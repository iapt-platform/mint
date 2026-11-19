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
 * GET /v3/tipitaka-word-paragraphs (+ /aggregate)：按词形找段落、按书统计（取代 v2 search-pali-wbw / -books）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function wbwToken(int $book, int $para, string $real, array $attrs = []): void
{
    WbwTemplate::forceCreate($attrs + [
        'book' => $book, 'paragraph' => $para, 'wid' => 1, 'word' => $real, 'real' => $real,
        'type' => '', 'gramma' => '', 'part' => '', 'style' => '', 'pcd_book_id' => $book, 'weight' => 1,
    ]);
}

function wordPara(int $book, int $para, string $html, array $attrs = []): PaliText
{
    return PaliText::forceCreate($attrs + [
        'book' => $book, 'paragraph' => $para, 'level' => 100, 'class' => '', 'toc' => '', 'text' => '',
        'html' => $html, 'uid' => (string) Str::uuid(),
        'path' => json_encode([
            ['book' => $book, 'paragraph' => 1, 'title' => "Book {$book}", 'level' => '1'],
            ['book' => $book, 'paragraph' => 2, 'title' => 'Chapter', 'level' => 2],
        ]),
    ]);
}

it('lists matching paragraphs by weight with path, safe highlighted text and page refs', function () {
    wordPara(1, 10, '<p class="bodytext">Vitakketīti <span class="bld">vitakko</span>. <script>x</script> &amp; vitakkaṃ</p>');
    wbwToken(1, 10, 'vitakko', ['style' => 'bld', 'weight' => 10]);
    wbwToken(1, 10, 'vitakkaṃ', ['weight' => 1]);
    wordPara(1, 20, '<p>vitakko</p>');
    wbwToken(1, 20, 'vitakko', ['weight' => 1]);
    PageNumber::forceCreate(['type' => 'P', 'volume' => 2, 'page' => 300, 'book' => 1, 'paragraph' => 10, 'wid' => 5, 'pcd_book_id' => 1]);
    PageNumber::forceCreate(['type' => 'P', 'volume' => 2, 'page' => 299, 'book' => 1, 'paragraph' => 10, 'wid' => 1, 'pcd_book_id' => 1]);

    $res = $this->getJson('/api/v3/tipitaka-word-paragraphs?words=vitakko,vitakkaṃ')->assertOk();

    $res->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.*.id', ['1-10', '1-20'])
        ->assertJsonPath('data.0.score', 11)
        ->assertJsonPath('data.0.hits', 2)
        ->assertJsonPath('data.0.title', 'Chapter')
        ->assertJsonPath('data.0.path.0', ['book' => 1, 'para' => 1, 'title' => 'Book 1', 'level' => 1])
        ->assertJsonPath('data.0.refs.0', ['type' => 'PTS', 'volume' => 2, 'page' => 299, 'title' => 'Nam'])
        ->assertJsonPath('data.0.refs.1.type', 'WP');

    // 只有 <b> 与 <mark>，其余已转义；「Vitakketīti」不是命中词形，不标
    expect($res->json('data.0.text'))
        ->toBe('Vitakketīti <b><mark>vitakko</mark></b>. x &amp; <mark>vitakkaṃ</mark>');
});

it('filters bold-only, bold-excluded and by book_id', function () {
    wordPara(1, 1, '<p>a</p>');
    wordPara(2, 1, '<p>b</p>');
    wbwToken(1, 1, 'vitakko', ['style' => 'bld']);
    wbwToken(2, 1, 'vitakko');

    $this->getJson('/api/v3/tipitaka-word-paragraphs?words=vitakko&bold=only')->assertJsonPath('data.*.id', ['1-1']);
    $this->getJson('/api/v3/tipitaka-word-paragraphs?words=vitakko&bold=exclude')->assertJsonPath('data.*.id', ['2-1']);
    $this->getJson('/api/v3/tipitaka-word-paragraphs?words=vitakko&book_id=2')->assertJsonPath('data.*.id', ['2-1']);
});

it('never matches the blank tokens when words has empty items', function () {
    wordPara(1, 1, '<p>a</p>');
    wbwToken(1, 1, '');

    $this->getJson('/api/v3/tipitaka-word-paragraphs?words=,,')->assertStatus(422)->assertJsonValidationErrors('words');
});

it('rejects invalid parameters', function (array $query, string $field) {
    $this->getJson('/api/v3/tipitaka-word-paragraphs?'.http_build_query($query))
        ->assertStatus(422)
        ->assertJsonValidationErrors($field);
})->with([
    'missing words' => [[], 'words'],
    'bad bold' => [['words' => 'x', 'bold' => 'on'], 'bold'],
    'bad book_id' => [['words' => 'x', 'book_id' => '1,a'], 'book_id'],
    'per_page too large' => [['words' => 'x', 'per_page' => 101], 'per_page'],
    'too many words' => [['words' => implode(',', range(1, 201))], 'words'],
]);

it('does not query per row', function () {
    $queries = function (int $rows): int {
        WbwTemplate::query()->delete();
        for ($i = 1; $i <= $rows; $i++) {
            wordPara(1, 100 + $i, '<p>vitakko</p>');
            wbwToken(1, 100 + $i, 'vitakko');
            PageNumber::forceCreate(['type' => 'M', 'volume' => 1, 'page' => $i, 'book' => 1, 'paragraph' => 100 + $i, 'wid' => 1, 'pcd_book_id' => 1]);
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson('/api/v3/tipitaka-word-paragraphs?words=vitakko')->assertOk()->assertJsonCount($rows, 'data');

        return count(DB::getQueryLog());
    };

    expect($queries(2))->toBe($queries(10));
});

it('aggregates paragraphs and occurrences per book with layer from the book tags', function () {
    $head = wordPara(5, 3, '', ['level' => 1, 'toc' => 'Dhammasaṅgaṇīpāḷi']);
    BookTitle::forceCreate(['sn' => 79, 'book' => 5, 'paragraph' => 3, 'title' => 'dhs']);
    $owner = (string) Str::uuid();
    foreach (['abhidhamma', 'mūla'] as $name) {
        $tag = Tag::forceCreate(['name' => $name, 'owner_id' => $owner]);
        TagMap::forceCreate(['table_name' => 'pali_texts', 'anchor_id' => $head->uid, 'tag_id' => $tag->id]);
    }
    wbwToken(5, 10, 'vitakko', ['pcd_book_id' => 79]);
    wbwToken(5, 10, 'vitakkaṃ', ['pcd_book_id' => 79]);
    wbwToken(5, 11, 'vitakko', ['pcd_book_id' => 79]);
    wbwToken(9, 1, 'vitakko', ['pcd_book_id' => 99]);   // 没有 book_titles 的书

    $this->getJson('/api/v3/tipitaka-word-paragraphs/aggregate?words=vitakko,vitakkaṃ')
        ->assertOk()
        ->assertJsonPath('meta', ['total' => 2, 'paragraphs' => 3, 'occurrences' => 4])
        ->assertJsonPath('data.0.book_id', 79)
        ->assertJsonPath('data.0.title', 'Dhammasaṅgaṇīpāḷi')
        ->assertJsonPath('data.0.layer', 'mula')
        ->assertJsonPath('data.0.paragraphs', 2)
        ->assertJsonPath('data.0.occurrences', 3)
        ->assertJsonPath('data.1.book_id', 99)
        ->assertJsonPath('data.1.layer', null);
});

it('requires words on the aggregate endpoint', function () {
    $this->getJson('/api/v3/tipitaka-word-paragraphs/aggregate')->assertStatus(422)->assertJsonValidationErrors('words');
});
