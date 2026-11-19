<?php

use App\Models\WordIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/*
 * GET /v3/pali-word-forms/{word}：词 → 词根 → 三藏里出现过的词形与次数（取代 v2 case/{word}）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function wordIndex(string $word, int $count, int $bold = 0): void
{
    WordIndex::forceCreate([
        'id' => random_int(1, PHP_INT_MAX >> 33),
        'word' => $word,
        'word_en' => $word,
        'count' => $count,
        'normal' => $count - $bold,
        'bold' => $bold,
        'is_base' => 0,
        'len' => mb_strlen($word),
    ]);
}

it('lists the forms of the word itself first, sorted by count, with bold counts', function () {
    wordIndex('vitakko', 1070, 52);
    wordIndex('vitakkaṃ', 236, 21);
    wordIndex('vitakkassāti', 3, 2);   // 连音 iti
    wordIndex('unrelated', 99);

    $res = $this->getJson('/api/v3/pali-word-forms/vitakka')->assertOk();

    $res->assertJsonPath('data.0.base', 'vitakka')
        ->assertJsonPath('data.0.forms', [
            ['word' => 'vitakko', 'count' => 1070, 'bold' => 52],
            ['word' => 'vitakkaṃ', 'count' => 236, 'bold' => 21],
            ['word' => 'vitakkassāti', 'count' => 3, 'bold' => 2],
        ])
        ->assertJsonPath('meta.total', count($res->json('data')));

    expect(collect($res->json('data'))->pluck('forms')->flatten(1)->pluck('word'))->not->toContain('unrelated');
});

it('is case-insensitive', function () {
    wordIndex('vitakko', 10);

    $this->getJson('/api/v3/pali-word-forms/Vitakka')
        ->assertOk()
        ->assertJsonPath('data.0.base', 'vitakka');
});

it('returns an empty list when no form occurs in the canon', function () {
    $this->getJson('/api/v3/pali-word-forms/qqqzzz')
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
});

it('rejects an over-long word with 404 (path constraint)', function () {
    $this->getJson('/api/v3/pali-word-forms/'.str_repeat('a', 65))->assertNotFound();
});

it('looks up all candidate forms in a single query', function () {
    wordIndex('vitakko', 10);
    wordIndex('dhammo', 10);

    $queries = function (string $word): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson("/api/v3/pali-word-forms/{$word}")->assertOk();

        return count(DB::getQueryLog());
    };

    // 词根多少、候选词形多少都不影响查询次数
    expect($queries('vitakka'))->toBe($queries('dhammo'))->toBe(1);
});
