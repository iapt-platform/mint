<?php

use App\Models\AiModel;
use App\Models\Channel;
use App\Models\ChannelLink;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * GET / PATCH /v3/channels/{channel}/links：channel 之间的关系设置。
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 */

function linksUrl(string $channel): string
{
    return "/api/v3/channels/{$channel}/links";
}

function setChannel(string $uid, array $attrs): void
{
    Channel::where('uid', $uid)->update($attrs);
}

it('saves all four kinds and returns them in order', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner, 'en');
    $zh = makeChannel($owner, 'zh');
    $wbw = makeChannel($owner, 'wbw');
    setChannel($wbw, ['type' => 'wbw']);
    [$a, $b, $c] = [makeChannel($owner, 'a'), makeChannel($owner, 'b'), makeChannel($owner, 'c')];

    $this->patchJson(linksUrl($channel), [
        'derived_from' => $zh, 'wbw' => $wbw, 'fallback' => [$b, $a], 'term_source' => [$c, $zh],
    ], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.channel_id', $channel)
        ->assertJsonPath('data.derived_from.id', $zh)
        ->assertJsonPath('data.derived_from.name', 'zh')
        ->assertJsonPath('data.wbw.type', 'wbw')
        ->assertJsonPath('data.fallback.*.id', [$b, $a])
        ->assertJsonPath('data.term_source.*.id', [$c, $zh])
        ->assertJsonPath('data.can_update', true);

    $this->getJson(linksUrl($channel))
        ->assertOk()
        ->assertJsonPath('data.fallback.*.id', [$b, $a])
        ->assertJsonPath('data.can_update', false);
});

it('returns empty settings for a channel without links', function () {
    $channel = makeChannel(makeStudio('owner'));

    $this->getJson(linksUrl($channel))
        ->assertOk()
        ->assertJsonPath('data.derived_from', null)
        ->assertJsonPath('data.wbw', null)
        ->assertJsonPath('data.fallback', [])
        ->assertJsonPath('data.term_source', []);
});

it('replaces only the kinds that were sent, and clears with null or []', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    [$a, $b] = [makeChannel($owner, 'a'), makeChannel($owner, 'b')];

    $this->patchJson(linksUrl($channel), ['derived_from' => $a, 'fallback' => [$a, $b]], authHeader($owner))->assertOk();
    $this->patchJson(linksUrl($channel), ['fallback' => [$b]], authHeader($owner))
        ->assertJsonPath('data.derived_from.id', $a)
        ->assertJsonPath('data.fallback.*.id', [$b]);
    $this->patchJson(linksUrl($channel), ['derived_from' => null, 'fallback' => []], authHeader($owner))
        ->assertJsonPath('data.derived_from', null)
        ->assertJsonPath('data.fallback', []);

    expect(ChannelLink::where('channel_uid', $channel)->count())->toBe(0);
});

it('hides targets that were deleted or that the viewer cannot see', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    [$private, $gone, $public] = [makeChannel($owner, 'private'), makeChannel($owner, 'gone'), makeChannel($owner, 'public')];
    $this->patchJson(linksUrl($channel), ['term_source' => [$private, $gone, $public]], authHeader($owner))->assertOk();
    setChannel($private, ['status' => Channel::STATUS_PRIVATE]);
    Channel::where('uid', $gone)->first()->delete();

    $this->getJson(linksUrl($channel))->assertJsonPath('data.term_source.*.id', [$public]);
    $this->getJson(linksUrl($channel), authHeader($owner))->assertJsonPath('data.term_source.*.id', [$private, $public]);
});

it('lets only those who can update the channel change its links', function () {
    [$team, $users] = makeTeam();
    $channel = makeChannel($team);
    $target = makeChannel($team, 'target');

    $this->patchJson(linksUrl($channel), ['derived_from' => $target])->assertUnauthorized();
    foreach (['owner', 'manager', 'editor'] as $role) {
        $this->patchJson(linksUrl($channel), ['derived_from' => $target], authHeader($users[$role]))->assertOk();
    }
    foreach (['reader', 'outsider'] as $role) {
        $this->patchJson(linksUrl($channel), ['derived_from' => $target], authHeader($users[$role]))->assertForbidden();
    }

    $model = AiModel::factory()->ownedBy($users['owner'])->create();
    $this->patchJson(linksUrl($channel), ['derived_from' => $target], [
        'Authorization' => 'Bearer '.AuthService::getUserToken($model->uid),
    ])->assertForbidden();
});

it('hides the settings of a private channel from those who cannot see it', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    setChannel($channel, ['status' => Channel::STATUS_PRIVATE]);

    $this->getJson(linksUrl($channel))->assertForbidden();
    $this->getJson(linksUrl($channel), authHeader(makeStudio('stranger')))->assertForbidden();
    $this->getJson(linksUrl($channel), authHeader($owner))->assertOk();
    $this->getJson(linksUrl((string) Str::uuid()))->assertNotFound();
});

it('rejects invalid targets with field errors', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $notWbw = makeChannel($owner, 'translation');
    $hidden = makeChannel(makeStudio('other'), 'hidden');
    setChannel($hidden, ['status' => Channel::STATUS_PRIVATE]);
    $headers = authHeader($owner);

    $this->patchJson(linksUrl($channel), ['derived_from' => $channel], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('derived_from');
    $this->patchJson(linksUrl($channel), ['fallback' => [$notWbw, $hidden]], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('fallback.1');
    $this->patchJson(linksUrl($channel), ['term_source' => [(string) Str::uuid()]], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('term_source.0');
    $this->patchJson(linksUrl($channel), ['wbw' => $notWbw], $headers)
        ->assertUnprocessable()->assertJsonValidationErrors('wbw');

    expect(ChannelLink::count())->toBe(0);
});

it('requires fallback channels of the same type as the channel', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);                    // translation
    $same = makeChannel($owner, 'same');
    $nissaya = makeChannel($owner, 'nissaya');
    setChannel($nissaya, ['type' => 'nissaya']);

    $this->patchJson(linksUrl($channel), ['fallback' => [$same, $nissaya]], authHeader($owner))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('fallback.1')
        ->assertJsonMissingValidationErrors('fallback.0');

    $other = makeChannel($owner, 'nissaya-2');
    setChannel($other, ['type' => 'nissaya']);
    $this->patchJson(linksUrl($nissaya), ['fallback' => [$other]], authHeader($owner))
        ->assertOk()
        ->assertJsonPath('data.fallback.*.id', [$other]);
});

it('allows term sources only on translation channels, and only translation targets', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);                    // translation
    $translation = makeChannel($owner, 'translation');
    $nissaya = makeChannel($owner, 'nissaya');
    setChannel($nissaya, ['type' => 'nissaya']);

    $this->patchJson(linksUrl($channel), ['term_source' => [$translation, $nissaya]], authHeader($owner))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('term_source.1')
        ->assertJsonMissingValidationErrors('term_source.0');

    // 非 translation 的 channel 不能设术语路径，但清空总是允许
    $this->patchJson(linksUrl($nissaya), ['term_source' => [$translation]], authHeader($owner))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('term_source');
    $this->patchJson(linksUrl($nissaya), ['term_source' => []], authHeader($owner))->assertOk();

    expect(ChannelLink::count())->toBe(0);
});

it('validates the shape of the input', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    $a = makeChannel($owner, 'a');
    $many = array_map(fn ($i) => makeChannel($owner, "c{$i}"), range(1, 4));

    $this->patchJson(linksUrl($channel), ['derived_from' => 'nope'], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('derived_from');
    $this->patchJson(linksUrl($channel), ['fallback' => $many], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('fallback');
    $this->patchJson(linksUrl($channel), ['term_source' => [$a, $a]], authHeader($owner))
        ->assertUnprocessable()->assertJsonValidationErrors('term_source.0');
});

it('never exposes the auto-increment id of channel_links', function () {
    $owner = makeStudio('owner');
    $channel = makeChannel($owner);
    [$a, $b] = [makeChannel($owner, 'a'), makeChannel($owner, 'b')];
    $patched = $this->patchJson(linksUrl($channel), ['derived_from' => $a, 'fallback' => [$b], 'term_source' => [$a, $b]], authHeader($owner))
        ->assertOk();
    $shown = $this->getJson(linksUrl($channel), authHeader($owner))->assertOk();

    $rowIds = ChannelLink::where('channel_uid', $channel)->pluck('id')->all();
    expect($rowIds)->toHaveCount(4);
    foreach ([$patched, $shown] as $res) {
        $data = $res->json('data');
        expect(array_keys($data))->toBe(['channel_id', 'derived_from', 'wbw', 'fallback', 'term_source', 'can_update']);
        $ids = [$data['channel_id'], $data['derived_from']['id'], ...array_column($data['fallback'], 'id'), ...array_column($data['term_source'], 'id')];
        foreach ($ids as $id) {
            expect(Str::isUuid($id))->toBeTrue();
        }
        foreach ([$data['derived_from'], ...$data['fallback'], ...$data['term_source']] as $ref) {
            expect(array_keys($ref))->toBe(['id', 'name', 'type', 'lang', 'status']);
        }
        expect(array_intersect($rowIds, $ids))->toBe([]);
    }
});

it('reads the settings in a constant number of queries', function () {
    $owner = makeStudio('owner');
    $count = function (int $targets) use ($owner) {
        $channel = makeChannel($owner);
        $uids = array_map(fn ($i) => makeChannel($owner, "t{$targets}-{$i}"), range(1, $targets));
        $this->patchJson(linksUrl($channel), ['term_source' => $uids], authHeader($owner))->assertOk();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson(linksUrl($channel), authHeader($owner))->assertOk();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $n;
    };

    expect($count(8))->toBe($count(2));
});
