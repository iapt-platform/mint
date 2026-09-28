<?php

use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\RelatedParagraph;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * GET /v3/tipitaka-related-paragraphs 与 /aggregate —— 段落关联关系的查询与聚合。
 *
 * 取代 v2 的 related-paragraph：保留 book+para 锚点定位，新增 book_name/cs_para
 * 过滤器与按维度聚合；同时把旧 Resource 的逐行 N+1 收敛成批量查询。
 */
function makeRelatedParagraph(int $book, int $para, int $bookId, int $csPara, string $bookName): RelatedParagraph
{
    $row = new RelatedParagraph;
    $row->forceFill([
        'book' => $book,
        'para' => $para,
        'book_id' => $bookId,
        'cs_para' => $csPara,
        'book_name' => $bookName,
    ])->save();

    return $row;
}

function makeBookTitle(int $sn, int $book, int $paragraph, string $title): BookTitle
{
    $row = new BookTitle;
    $row->forceFill(['sn' => $sn, 'book' => $book, 'paragraph' => $paragraph, 'title' => $title])->save();

    return $row;
}

function makeRelatedPaliText(int $book, int $paragraph, array $attrs = []): PaliText
{
    $row = new PaliText;
    $row->forceFill(array_merge([
        'book' => $book,
        'paragraph' => $paragraph,
        'level' => 1,
        'class' => '',
        'toc' => '',
        'text' => '',
        'html' => '',
        'uid' => (string) Str::uuid(),
    ], $attrs))->save();

    return $row;
}

function makeTag(string $name, int $color = 0): Tag
{
    $tag = new Tag;
    $tag->forceFill(['id' => (string) Str::uuid(), 'name' => $name, 'color' => $color, 'owner_id' => (string) Str::uuid()])->save();

    return $tag;
}

it('lists related paragraphs grouped by book with title, tags and path', function () {
    makeRelatedParagraph(1, 2, 10, 4, 'dn1');
    makeRelatedParagraph(3, 5, 20, 4, 'dn1');

    makeBookTitle(10, 1, 100, 'dīghanikāyapāḷi');
    makeBookTitle(20, 3, 200, 'sumaṅgalavilāsinī');
    $root = makeRelatedPaliText(1, 100);          // book_id=10 的书名行 → uid 供 tags
    $otherRoot = makeRelatedPaliText(3, 200);     // book_id=20 的书名行
    makeRelatedPaliText(1, 2, ['path' => json_encode([['title' => 'path-a', 'level' => 1]])]);
    makeRelatedPaliText(3, 5, ['path' => json_encode([['title' => 'path-b', 'level' => 1]])]);

    $pali = makeTag('pāḷi', 1);
    $attha = makeTag('aṭṭhakathā', 2);
    (new TagMap)->forceFill(['id' => (string) Str::uuid(), 'table_name' => 'pali_texts', 'anchor_id' => $root->uid, 'tag_id' => $pali->id])->save();
    (new TagMap)->forceFill(['id' => (string) Str::uuid(), 'table_name' => 'pali_texts', 'anchor_id' => $otherRoot->uid, 'tag_id' => $attha->id])->save();

    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs?book=1&para=2')->assertOk()->json();

    expect($json['data'])->toHaveCount(2)
        ->and($json['meta']['count'])->toBe(2)
        ->and($json['data'][0])->toMatchArray([
            'book' => 1,
            'book_id' => 10,
            'title' => 'dīghanikāyapāḷi',
            'cs_para' => 4,
            'para' => [2],
        ])
        ->and($json['data'][0]['tags'])->toBe(['pāḷi'])
        ->and($json['data'][0]['path'][0]['title'])->toBe('path-a')
        ->and($json['data'][1]['title'])->toBe('sumaṅgalavilāsinī')
        ->and($json['data'][1]['tags'])->toBe(['aṭṭhakathā']);
});

it('filters directly by book_name and cs_para', function () {
    makeRelatedParagraph(1, 2, 10, 4, 'dn1');
    makeRelatedParagraph(3, 5, 20, 4, 'dn1');
    makeRelatedParagraph(3, 6, 20, 5, 'dn1'); // 不同 cs_para，不该命中

    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs?book_name=dn1&cs_para=4')->assertOk()->json();

    expect($json['data'])->toHaveCount(2)
        ->and($json['meta']['count'])->toBe(2);
});

it('returns an empty set when the paragraph has no anchor', function () {
    makeRelatedParagraph(1, 2, 10, 0, 'dn1'); // cs_para=0：无锚点（书前页）

    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs?book=1&para=2')->assertOk()->json();

    expect($json['data'])->toBe([])
        ->and($json['meta']['count'])->toBe(0);
});

it('lists distinct book names', function () {
    makeRelatedParagraph(100, 2, 110, 4, 'an2');
    makeRelatedParagraph(100, 3, 110, 4, 'an3');
    makeRelatedParagraph(101, 2, 111, 5, 'an2');

    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate')->assertOk()->json();

    expect($json['data'])->toBe([['book_name' => 'an2'], ['book_name' => 'an3']])
        ->and($json['meta']['total'])->toBe(2);
});

it('lists cs_para of a book name', function () {
    makeRelatedParagraph(100, 2, 110, 4, 'an2');
    makeRelatedParagraph(100, 3, 110, 5, 'an2');
    makeRelatedParagraph(101, 2, 111, 4, 'an2');
    makeRelatedParagraph(101, 3, 111, 5, 'an3');

    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate/an2')->assertOk()->json();

    expect($json['data'])->toBe([['cs_para' => '4'], ['cs_para' => '5']])
        ->and($json['meta']['total'])->toBe(2);
});

it('filters aggregate book names by file and book', function () {
    makeRelatedParagraph(100, 2, 110, 4, 'an2');
    makeRelatedParagraph(100, 3, 110, 5, 'an3');
    makeRelatedParagraph(101, 2, 111, 4, 'an2');

    // file 过滤 book 列：只剩 book=100 的两条
    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate?file=100')->assertOk()->json();
    expect($json['data'])->toBe([['book_name' => 'an2'], ['book_name' => 'an3']]);

    // book 过滤 book_id 列：只剩 book_id=110 的两条
    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate?book=110')->assertOk()->json();
    expect($json['data'])->toBe([['book_name' => 'an2'], ['book_name' => 'an3']]);

    // book 过滤 book_id 列：只剩 book_id=111 的一条
    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate?book=111')->assertOk()->json();
    expect($json['data'])->toBe([['book_name' => 'an2']]);
});

it('filters aggregate cs_para by file and book', function () {
    makeRelatedParagraph(100, 2, 110, 4, 'an2');
    makeRelatedParagraph(100, 3, 110, 5, 'an2');
    makeRelatedParagraph(101, 2, 111, 4, 'an2');

    // file 过滤 book 列：只剩 book=100 的两条
    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate/an2?file=100')->assertOk()->json();
    expect($json['data'])->toBe([['cs_para' => '4'], ['cs_para' => '5']]);

    // book 过滤 book_id 列：只剩 book_id=111 的一条
    $json = $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate/an2?book=111')->assertOk()->json();
    expect($json['data'])->toBe([['cs_para' => '4']]);
});

it('validates the mutually exclusive lookup filters', function () {
    $this->getJson('/api/v3/tipitaka-related-paragraphs?book=1')
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['para']]);

    $this->getJson('/api/v3/tipitaka-related-paragraphs?book=1&para=2&book_name=dn1')
        ->assertStatus(422);

    $this->getJson('/api/v3/tipitaka-related-paragraphs')
        ->assertStatus(422);
});

it('validates the aggregate endpoint pagination', function () {
    $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate?per_page=100000')
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['per_page']]);

    $this->getJson('/api/v3/tipitaka-related-paragraphs/aggregate/an2?per_page=100000')
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['per_page']]);
});

it('issues a constant number of queries regardless of book count', function () {
    makeRelatedParagraph(1, 1, 10, 7, 'dn1');
    makeRelatedParagraph(2, 1, 20, 7, 'dn1');
    makeRelatedParagraph(3, 1, 30, 7, 'dn1');
    makeBookTitle(10, 1, 100, 'a');
    makeBookTitle(20, 2, 100, 'b');
    makeBookTitle(30, 3, 100, 'c');

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->getJson('/api/v3/tipitaka-related-paragraphs?book=1&para=1')->assertOk();
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    // 再加一本：旧实现会每本多打 5 条 SQL，这里查询数必须保持常量
    makeRelatedParagraph(4, 1, 40, 7, 'dn1');
    makeBookTitle(40, 4, 100, 'd');

    DB::flushQueryLog();
    DB::enableQueryLog();
    $this->getJson('/api/v3/tipitaka-related-paragraphs?book=1&para=1')->assertOk();
    expect(count(DB::getQueryLog()))->toBe($queries);
    DB::disableQueryLog();
});
