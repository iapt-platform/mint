<?php

use App\Models\WordIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * GET /v3/pali-word-suggestions：搜索框的巴利词补全（取代 v2 pali-word-index?view=key）。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function suggestWord(string $word, string $wordEn, int $count, int $bold = 0): void
{
    WordIndex::forceCreate([
        'id' => random_int(1, PHP_INT_MAX >> 33),
        'word' => $word, 'word_en' => $wordEn, 'count' => $count, 'normal' => $count - $bold,
        'bold' => $bold, 'is_base' => 0, 'len' => mb_strlen($word),
    ]);
}

it('completes a prefix, exact match first then by count, matching words without diacritics', function () {
    suggestWord('dhammā', 'dhamma', 19436, 413);
    suggestWord('dhammaṃ', 'dhammam', 19963, 739);
    suggestWord('dhammassa', 'dhammassa', 7423, 64);
    suggestWord('adhamma', 'adhamma', 500);       // 不是前缀

    $this->getJson('/api/v3/pali-word-suggestions?q=Dhamma')
        ->assertOk()
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('data', [
            ['word' => 'dhammā', 'count' => 19436, 'bold' => 413],   // 去变音后与输入相同
            ['word' => 'dhammaṃ', 'count' => 19963, 'bold' => 739],
            ['word' => 'dhammassa', 'count' => 7423, 'bold' => 64],
        ]);

    $this->getJson('/api/v3/pali-word-suggestions?q=dhamma&limit=1')->assertJsonCount(1, 'data');
});

it('treats LIKE wildcards in the input literally', function () {
    suggestWord('dhammo', 'dhammo', 10);

    $this->getJson('/api/v3/pali-word-suggestions?q=dh%25')->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/v3/pali-word-suggestions?q=dh_mmo')->assertOk()->assertJsonCount(0, 'data');
});

it('rejects a missing prefix and a limit out of range', function () {
    $this->getJson('/api/v3/pali-word-suggestions')->assertStatus(422)->assertJsonValidationErrors('q');
    $this->getJson('/api/v3/pali-word-suggestions?q=a&limit=51')->assertStatus(422)->assertJsonValidationErrors('limit');
});
