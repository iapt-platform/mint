<?php

use App\Models\Channel;
use App\Models\ProgressChapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * 阅读领域的 channel 查询：
 *   GET /api/v3/tipitaka-paragraph-channels/{book}/{para}   某段有内容的 channel（取代 channel?view=paragraphs）
 *   GET /api/v3/tipitaka-channels                           有译文的公开 channel 目录（取代 progress?view=channel）
 *   GET /api/v3/tipitaka-chapter-channels/{book}/{para}     （已有）这里只补团队名下私有 channel 的可见性
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */
function chapterProgress(string $channel, int $book, int $para, float $progress, string $lang = 'zh'): void
{
    (new ProgressChapter)->forceFill([
        'uid' => (string) Str::uuid(), 'book' => $book, 'para' => $para, 'channel_id' => $channel,
        'lang' => $lang, 'all_trans' => 100, 'public' => 100, 'progress' => $progress,
    ])->save();
}

function hiddenChannel(string $ownerUid, string $name): string
{
    $uid = makeChannel($ownerUid, $name);
    Channel::where('uid', $uid)->update(['status' => Channel::STATUS_PRIVATE]);

    return $uid;
}

/* ---------------- 某段有内容的 channel ---------------- */

it('lists channels with content in a paragraph, honouring visibility', function () {
    [$team, $users] = makeTeam();
    $public = makeChannel(makeStudio('a'), 'public');
    $teamPrivate = hiddenChannel($team, 'team private');
    $otherPrivate = hiddenChannel(makeStudio('b'), 'other private');
    $elsewhere = makeChannel(makeStudio('c'), 'elsewhere');
    foreach ([$public, $teamPrivate, $otherPrivate] as $channel) {
        makeSentence($channel, 5, 7, 1, 'text');
    }
    makeSentence($elsewhere, 5, 8, 1, 'text');

    $ids = fn (array $headers = []) => array_column(
        $this->getJson('/api/v3/tipitaka-paragraph-channels/5/7', $headers)->assertOk()->json('data'),
        'id'
    );

    expect($ids())->toBe([$public]);
    expect($ids(authHeader($users['reader'])))->toEqualCanonicalizing([$public, $teamPrivate]);
});

it('returns 404 for a non-numeric paragraph', function () {
    $this->getJson('/api/v3/tipitaka-paragraph-channels/5/abc')->assertNotFound();
});

/* ---------------- 有译文的公开 channel 目录 ---------------- */

it('lists public channels with translated chapters, most chapters first', function () {
    $owner = makeStudio('owner');
    $big = makeChannel($owner, 'big');
    $small = makeChannel($owner, 'small');
    $hidden = hiddenChannel($owner, 'hidden');
    $english = makeChannel($owner, 'english');
    foreach ([1, 2, 3] as $para) {
        chapterProgress($big, 1, $para, 0.95);
    }
    chapterProgress($small, 1, 1, 0.9);
    chapterProgress($small, 1, 2, 0.5); // 没过门槛
    chapterProgress($hidden, 1, 1, 1.0);
    chapterProgress($english, 1, 1, 1.0, 'en');

    $data = $this->getJson('/api/v3/tipitaka-channels?lang=zh&type=translation')->assertOk()->json('data');

    expect(array_column($data, 'id'))->toBe([$big, $small]);
    expect(array_column($data, 'chapter_count'))->toBe([3, 1]);
    expect($data[0]['owner']['id'])->toBe($owner);

    // 门槛可调
    expect(array_column($this->getJson('/api/v3/tipitaka-channels?lang=zh&min_progress=0.4')->json('data'), 'chapter_count'))
        ->toBe([3, 2]);
});

it('validates the progress threshold', function () {
    $this->getJson('/api/v3/tipitaka-channels?min_progress=2')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('min_progress');
});

/* ---------------- 某章的 channel：团队名下的私有 channel ---------------- */

it('shows team-owned private channels of a chapter to team members only', function () {
    [$team, $users] = makeTeam();
    $teamPrivate = hiddenChannel($team, 'team private');
    chapterProgress($teamPrivate, 9, 9, 0.5);

    $ids = fn (array $headers = []) => array_column(
        $this->getJson('/api/v3/tipitaka-chapter-channels/9/9', $headers)->assertOk()->json('data'),
        'channel'
    );

    expect($ids())->toBe([]);
    expect($ids(authHeader($users['outsider'])))->toBe([]);
    expect(array_column($ids(authHeader($users['reader'])), 'id'))->toBe([$teamPrivate]);
});

it('keeps system channels out of a chapter\'s version list', function () {
    $pali = makeChannel(makeStudio('root'), '_System_Pali_VRI_');
    Channel::where('uid', $pali)->update(['is_system' => true, 'status' => Channel::STATUS_PRIVATE]);
    chapterProgress($pali, 9, 9, 1.0);

    expect($this->getJson('/api/v3/tipitaka-chapter-channels/9/9')->json('data'))->toBe([]);
});

it('drops soft-deleted channels from a chapter', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner, 'gone');
    chapterProgress($channel, 9, 9, 0.5);
    Channel::where('uid', $channel)->first()->delete();

    expect($this->getJson('/api/v3/tipitaka-chapter-channels/9/9')->json('data'))->toBe([]);
});
