<?php

use App\Models\Channel;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Tag;
use App\Models\TagMap;
use App\Services\V3\TipitakaChapterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * GET /v3/tipitaka-chapters：按名字找章节（容错）、列出一本书的标题。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function tcV3Title(int $book, int $para, int $level, string $toc, int $chapterLen = 10, ?array $path = null): PaliText
{
    $row = new PaliText;
    $row->forceFill([
        'book' => $book,
        'paragraph' => $para,
        'level' => $level,
        'class' => '',
        'toc' => $toc,
        'text' => $toc,
        'html' => '',
        'pcd_book_id' => 0,
        'chapter_len' => $chapterLen,
        'chapter_strlen' => $chapterLen * 100,
        'parent' => $level > 1 ? 3 : -1,
        'prev_chapter' => 0,
        'next_chapter' => -1,
        'path' => json_encode($path ?? [['book' => $book, 'paragraph' => 3, 'title' => 'book', 'level' => '1']]),
        'uid' => (string) Str::uuid(),
    ])->save();

    return $row;
}

function tcV3Tag(PaliText $book, string $name): void
{
    $tag = Tag::where('name', $name)->first();
    if (! $tag) {
        $tag = new Tag;
        $tag->forceFill(['id' => (string) Str::uuid(), 'name' => $name, 'color' => 0, 'owner_id' => (string) Str::uuid()])->save();
    }
    (new TagMap)->forceFill([
        'id' => (string) Str::uuid(), 'table_name' => 'pali_texts', 'anchor_id' => $book->uid, 'tag_id' => $tag->id,
    ])->save();
}

function tcV3Translation(int $book, int $para, string $title, int $status = 30, string $lang = 'zh'): void
{
    $channel = makeChannel((string) Str::uuid());
    Channel::where('uid', $channel)->update(['status' => $status]);
    (new ProgressChapter)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'book' => $book,
        'para' => $para,
        'lang' => $lang,
        'channel_id' => $channel,
        'progress' => 0.5,
        'title' => $title."\n",
        'all_trans' => 0,
        'public' => 0,
    ])->save();
}

/**
 * 根本典 book 94（长部大品）与它的义注 book 104，各一部经；另有一部 anna 书里同名的短章。
 */
function tcV3Fixture(): void
{
    tcV3Tag(tcV3Title(94, 3, 1, '(DN) Mahāvaggapāḷi', 1000), 'mūla');
    tcV3Title(94, 287, 2, '3. Mahāparinibbānasuttaṃ', 376);
    tcV3Title(94, 585, 4, 'Tathāgatapacchimavācā', 4);
    tcV3Title(94, 663, 2, '4. Mahāsudassanasuttaṃ', 74);
    tcV3Tag(tcV3Title(104, 3, 1, '(DN) Mahāvaggaṭṭhakathā', 1000), 'aṭṭhakathā');
    tcV3Title(104, 420, 2, '3. Mahāparinibbānasuttavaṇṇanā', 300);
    tcV3Title(162, 3809, 4, '3. Ānandattheragāthā', 112);
    tcV3Title(162, 1539, 4, '3. Girimānandattheragāthā', 12);
    tcV3Translation(94, 287, '大般涅槃经');
    tcV3Translation(94, 663, '私密译名', 10);
}

it('lists a book in canonical order with coordinates and size', function () {
    tcV3Fixture();

    $response = $this->getJson('/api/v3/tipitaka-chapters?book=94')->assertOk();

    expect(array_column($response->json('data'), 'para'))->toBe([3, 287, 585, 663]);
    $sutta = $response->json('data.1');
    expect($sutta)->toMatchArray([
        'book' => 94, 'para' => 287, 'level' => 2, 'title' => '3. Mahāparinibbānasuttaṃ',
        'layer' => 'mula', 'paragraphs' => 376, 'chars' => 37600, 'parent' => 3, 'prev' => null, 'next' => null,
    ])->and($sutta['translations'])->toBe([['lang' => 'zh', 'title' => '大般涅槃经']])
        ->and($sutta['path'][0])->toBe(['book' => 94, 'para' => 3, 'title' => 'book', 'level' => 1])
        ->and($sutta)->not->toHaveKey('score');
    expect($response->json('meta.total'))->toBe(4);
});

it('narrows a book listing to one chapter and by level', function () {
    tcV3Fixture();

    $this->getJson('/api/v3/tipitaka-chapters?book=94&para=287')
        ->assertOk()
        ->assertJsonPath('data.*.para', [287, 585]);

    $this->getJson('/api/v3/tipitaka-chapters?book=94&max_level=2')
        ->assertOk()
        ->assertJsonPath('data.*.para', [3, 287, 663]);
});

it('finds a title regardless of case and diacritics, canonical text first', function () {
    tcV3Fixture();

    $data = $this->getJson('/api/v3/tipitaka-chapters?q=MAHAPARINIBBANA')->assertOk()->json('data');

    expect(array_map(fn ($r) => [$r['book'], $r['para'], $r['score']], $data))
        ->toBe([[94, 287, 0.95], [104, 420, 0.95]]);
});

it('tolerates misspellings and ranks the closer title first', function () {
    tcV3Fixture();

    $typo = $this->getJson('/api/v3/tipitaka-chapters?q=mahaparinibanasuta')->assertOk()->json('data');
    expect($typo[0]['para'])->toBe(287)
        ->and($typo[0]['score'])->toBeLessThan(0.86)->toBeGreaterThan(0.6);

    $ananda = $this->getJson('/api/v3/tipitaka-chapters?q=anandatheragatha')->assertOk()->json('data');
    expect($ananda[0]['para'])->toBe(3809);
});

it('finds a chapter by its public translated title only', function () {
    tcV3Fixture();

    $this->getJson('/api/v3/tipitaka-chapters?q='.urlencode('大般涅槃'))
        ->assertOk()
        ->assertJsonPath('data.*.para', [287]);

    $this->getJson('/api/v3/tipitaka-chapters?q='.urlencode('私密译名'))
        ->assertOk()
        ->assertJsonCount(0, 'data');
    $this->getJson('/api/v3/tipitaka-chapters?book=94&para=663')
        ->assertOk()
        ->assertJsonPath('data.0.translations', []);
});

it('filters by layer', function () {
    tcV3Fixture();

    $this->getJson('/api/v3/tipitaka-chapters?q=mahaparinibbana&layer=atthakatha')
        ->assertOk()
        ->assertJsonPath('data.*.book', [104]);
});

it('rejects a query without q or book, para without book, and unknown layers', function (string $query, string $field) {
    $this->getJson('/api/v3/tipitaka-chapters'.$query)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'no condition' => ['', 'q'],
    'para without book' => ['?q=x&para=287', 'book'],
    'unknown layer' => ['?book=94&layer=sutta', 'layer'],
    'per_page too large' => ['?book=94&per_page=5000', 'per_page'],
]);

it('scores tiers so that misspellings never outrank correct spellings', function () {
    expect(TipitakaChapterService::score('mahaparinibbana', 'mahaparinibbana')[0])->toBe(1.0)
        ->and(TipitakaChapterService::score('mahaparinibbana', 'mahaparinibbanasuttam')[0])->toBe(0.95)
        ->and(TipitakaChapterService::score('parinibbana', 'mahaparinibbanasuttam')[0])->toBe(0.9)
        ->and(TipitakaChapterService::score('mahaparinibanasuta', 'mahaparinibbanasuttam')[0])->toBeLessThanOrEqual(0.85)
        ->and(TipitakaChapterService::score('vessantara', 'mahaparinibbanasuttam')[0])->toBe(0.0)
        ->and(TipitakaChapterService::normalize('(DN) 3. Mahāparinibbānasuttaṃ'))->toBe('mahaparinibbanasuttam');
});
