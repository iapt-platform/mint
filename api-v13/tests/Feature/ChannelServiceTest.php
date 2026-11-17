<?php

use App\Http\Api\ChannelApi;
use App\Models\Channel;
use App\Services\ChannelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
 * ChannelService 是 ChannelApi 纯能力部分的新家；ChannelApi 那几个方法只剩转发壳，返回值必须与原来一致。
 */

it('summarises a channel the way v2 getById did', function () {
    $owner = makeStudio('owner');
    $uid = makeChannel($owner, 'my channel');

    $expected = [
        'id' => $uid, 'name' => 'my channel', 'type' => 'translation',
        'lang' => 'zh-Hans', 'studio_id' => $owner, 'status' => 30,
    ];
    expect(app(ChannelService::class)->summary($uid))->toBe($expected)
        ->and(ChannelApi::getById($uid))->toBe($expected)
        ->and(ChannelApi::getById('not-a-uuid'))->toBeFalse()
        ->and(ChannelApi::getById(null))->toBeFalse()
        ->and(ChannelApi::getById((string) Str::uuid()))->toBeFalse();
});

it('summarises many channels in input order with false for misses, in one query', function () {
    $owner = makeStudio('owner');
    $a = makeChannel($owner, 'a');
    $b = makeChannel($owner, 'b');
    $missing = (string) Str::uuid();

    DB::enableQueryLog();
    $result = ChannelApi::getByIds([$b, 'bad', $missing, $a, $b]);
    expect(DB::getQueryLog())->toHaveCount(1);

    expect(array_map(fn ($row) => $row === false ? false : $row['name'], $result))
        ->toBe(['b', false, false, 'a', 'b']);
});

it('finds a system channel by name under the root account, with a fallback', function () {
    $root = config('mint.admin.root_uuid');
    $en = makeChannel($root, '_System_Grammar_Term_en_');
    makeChannel(makeStudio('someone'), '_System_Grammar_Term_my_');   // 不是 root 名下的不算

    $service = app(ChannelService::class);
    expect($service->systemUid('_System_Grammar_Term_en_'))->toBe($en)
        ->and($service->systemUid('_System_Grammar_Term_my_'))->toBeFalse()
        ->and($service->systemUid('_System_Grammar_Term_my_', '_System_Grammar_Term_en_'))->toBe($en)
        ->and(ChannelApi::getSysChannel('_nope_', '_also_nope_'))->toBeFalse();
});

it('gets or creates the user book channel of a studio', function () {
    $studio = makeStudio('owner');
    $existing = makeChannel($studio, '_user_book_pali');

    expect(ChannelApi::userBookGetOrCreate($studio, 'pali', 30))->toBe($existing);

    $created = ChannelApi::userBookGetOrCreate($studio, 'zh-Hans', '10');
    expect($created)->toBeString()
        ->and(Channel::find($created))
        ->name->toBe('_user_book_zh-Hans')
        ->owner_uid->toBe($studio)
        ->is_system->toBeTrue()
        ->status->toBe(10);
    expect(ChannelApi::userBookGetOrCreate($studio, 'zh-Hans', 10))->toBe($created);
});

it('gets a channel by name or throws', function () {
    $uid = makeChannel(makeStudio('owner'), '_System_commentary_');

    expect(ChannelApi::getChannelByName('_System_commentary_')->uid)->toBe($uid);
    ChannelApi::getChannelByName('_missing_');
})->throws(Exception::class, 'channel is invalid');
