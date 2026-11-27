<?php

use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\RelatedParagraph;
use App\Models\WbwTemplate;
use App\Services\RelatedParagraph\RelatedParagraphSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * upgrade:related.paragraph：从 wbw_templates 重建 related_paragraphs，套 sn 书名表与补丁表。
 */

/**
 * 造一本 VRI book：段落 1..$count，一个 level-1 起于段落 1，$markers 是段落号 => 锚点原词。
 *
 * @param  array<int, list<string>>  $markers
 */
function makeRelatedBook(int $book, int $count, int $sn, array $markers): void
{
    foreach (range(1, $count) as $para) {
        (new PaliText)->forceFill([
            'book' => $book, 'paragraph' => $para, 'level' => $para === 1 ? 1 : 100,
            'class' => '', 'toc' => '', 'text' => '', 'html' => '', 'lenght' => 1,
            'pcd_book_id' => 0, 'uid' => (string) Str::uuid(),
        ])->save();
    }
    (new BookTitle)->forceFill(['book' => $book, 'paragraph' => 1, 'sn' => $sn, 'title' => "book {$book}"])->save();
    foreach ($markers as $para => $words) {
        foreach ($words as $wid => $word) {
            (new WbwTemplate)->forceFill([
                'book' => $book, 'paragraph' => $para, 'wid' => $wid + 1, 'word' => $word, 'real' => $word,
                'type' => '.ctl.', 'gramma' => '.a.', 'part' => '?', 'style' => '#a#',
            ])->save();
        }
    }
}

beforeEach(function () {
    $this->dataPath = sys_get_temp_dir().'/related-paragraphs-'.Str::random(8);
    mkdir($this->dataPath);
    app()->instance(RelatedParagraphSource::class, new RelatedParagraphSource($this->dataPath));
});

afterEach(function () {
    array_map('unlink', glob($this->dataPath.'/*'));
    rmdir($this->dataPath);
});

it('rebuilds rows from wbw anchors, expanding ranges', function () {
    makeRelatedBook(9101, 3, 9901, [1 => ['dn1'], 2 => ['para1'], 3 => ['para2-3', 'P1.0001']]);

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101]])->assertSuccessful();

    $rows = RelatedParagraph::query()->where('book', 9101)->orderBy('para')->orderBy('cs_para')
        ->get(['para', 'cs_para', 'book_name', 'book_id'])->toArray();
    expect($rows)->toBe([
        ['para' => 1, 'cs_para' => 0, 'book_name' => 'dn1', 'book_id' => 9901],
        ['para' => 2, 'cs_para' => 1, 'book_name' => 'dn1', 'book_id' => 9901],
        ['para' => 3, 'cs_para' => 2, 'book_name' => 'dn1', 'book_id' => 9901],
        ['para' => 3, 'cs_para' => 3, 'book_name' => 'dn1', 'book_id' => 9901],
    ]);
});

it('only replaces the books it was asked for', function () {
    makeRelatedBook(9101, 1, 9901, [1 => ['dn1', 'para1']]);
    makeRelatedBook(9102, 1, 9902, [1 => ['dn2', 'para1']]);
    RelatedParagraph::query()->insert(['book' => 9102, 'para' => 1, 'book_id' => 9902, 'cs_para' => 44, 'book_name' => 'old']);

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101]])->assertSuccessful();

    expect(RelatedParagraph::query()->where('book', 9102)->value('book_name'))->toBe('old');
});

it('names bare anchors from the sn table and applies the patch table', function () {
    makeRelatedBook(9101, 3, 9901, [1 => ['para1'], 2 => ['para2']]);
    file_put_contents($this->dataPath.'/book-names.csv', "sn,book_name\n9901,abhi3\n");
    file_put_contents($this->dataPath.'/9101.csv', "para,book_name,begin,end\n3,,10,11\n");

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101]])->assertSuccessful();

    $rows = RelatedParagraph::query()->where('book', 9101)->orderBy('para')->orderBy('cs_para')
        ->get(['para', 'cs_para', 'book_name'])->toArray();
    expect($rows)->toBe([
        ['para' => 1, 'cs_para' => 1, 'book_name' => 'abhi3'],
        ['para' => 2, 'cs_para' => 2, 'book_name' => 'abhi3'],
        ['para' => 3, 'cs_para' => 10, 'book_name' => 'abhi3'],
        ['para' => 3, 'cs_para' => 11, 'book_name' => 'abhi3'],
    ]);
});

it('writes no numbers for anchors that have no name', function () {
    makeRelatedBook(9101, 2, 9901, [1 => ['para5']]);

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101]])->assertSuccessful();

    expect(RelatedParagraph::query()->where('book', 9101)->pluck('cs_para')->all())->toBe([0, 0]);
});

it('writes nothing with --check', function () {
    makeRelatedBook(9101, 1, 9901, [1 => ['dn1', 'para1']]);

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101], '--check' => true])->assertSuccessful();

    expect(RelatedParagraph::query()->where('book', 9101)->count())->toBe(0);
});

it('rejects a patch row for a paragraph that does not exist', function () {
    makeRelatedBook(9101, 1, 9901, [1 => ['dn1', 'para1']]);
    file_put_contents($this->dataPath.'/9101.csv', "para,book_name,begin,end\n99,,1,1\n");

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101]]);
})->throws(RuntimeException::class, '9101.csv');

it('dumps the unpatched result in patch table format', function () {
    makeRelatedBook(9101, 2, 9901, [1 => ['dn1', 'para1']]);
    file_put_contents($this->dataPath.'/9101.csv', "para,book_name,begin,end\n2,,7,7\n");

    $this->artisan('upgrade:related.paragraph', ['--book' => [9101], '--dump' => true])
        ->expectsOutput('para,book_name,begin,end')
        ->expectsOutput('1,dn1,1,1')
        ->expectsOutput('2,dn1,1,1')
        ->assertSuccessful();
});
