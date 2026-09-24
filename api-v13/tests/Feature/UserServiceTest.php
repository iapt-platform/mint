<?php

use App\Http\Api\AiAssistantApi;
use App\Http\Api\UserApi;
use App\Models\AiModel;
use App\Models\UserInfo;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * UserService 取代 UserApi / AiAssistantApi 两个静态类。
 *
 * 这里断言两件事：
 *   1. 新实现的行为（批量、身份映射、占位口径）
 *   2. 转发壳与 Service 返回同一结果——v2 的六十多个调用点靠这条兜底
 */
function makeAssistant(string $name, array $attrs = []): AiModel
{
    $assistant = new AiModel;
    $assistant->forceFill(array_merge([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'name' => $name,
        'real_name' => $name.'-real',
        'model' => 'gpt-4',
        'url' => null,
        'avatar' => null,
        // ai_models 的 owner_id / editor_id 是 NOT NULL 且无默认值
        'owner_id' => (string) Str::uuid(),
        'editor_id' => (string) Str::uuid(),
    ], $attrs))->save();

    return $assistant;
}

it('resolves a human user', function () {
    $uid = makeStudio('alice');

    $profile = app(UserService::class)->byUuid($uid);

    expect($profile['id'])->toBe($uid)
        ->and($profile['nickName'])->toBe('alice')
        ->and($profile['userName'])->toBe('alice')
        ->and($profile['sn'])->toBeGreaterThan(0);
});

it('falls back to an ai assistant when the uuid is not a human', function () {
    $assistant = makeAssistant('claude');

    $profile = app(UserService::class)->byUuid($assistant->uid);

    expect($profile['id'])->toBe($assistant->uid)
        ->and($profile['nickName'])->toBe('claude')
        ->and($profile['roles'])->toBe(['ai'])
        ->and($profile['sn'])->toBe(0);
});

it('returns the unknown placeholder for an unresolvable uuid', function () {
    $profile = app(UserService::class)->byUuid((string) Str::uuid());

    expect($profile)->toBe([
        'id' => 0,
        'nickName' => 'unknown',
        'userName' => 'unknown',
        'realName' => 'unknown',
        'avatar' => '',
    ]);
});

it('costs no query for a null uuid', function () {
    DB::enableQueryLog();
    DB::flushQueryLog();
    $profile = app(UserService::class)->byUuid(null);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    // 旧实现会为 null 白查两张表
    expect($queries)->toBe(0)
        ->and($profile['nickName'])->toBe('unknown');
});

it('resolves a mixed batch in two queries regardless of size', function () {
    $humans = collect(range(1, 6))->map(fn ($i) => makeStudio("batch-user-{$i}"))->all();
    $assistants = collect(range(1, 3))->map(fn ($i) => makeAssistant("batch-ai-{$i}")->uid)->all();
    $orphan = (string) Str::uuid();

    DB::enableQueryLog();
    DB::flushQueryLog();
    $map = app(UserService::class)->byUuids([...$humans, ...$assistants, $orphan]);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    // user_infos 一次 + ai_models 一次，与 uuid 个数无关
    expect($queries)->toBe(2)
        ->and($map)->toHaveCount(9)          // 解析不到的 orphan 不出现在结果里
        ->and($map)->not->toHaveKey($orphan)
        ->and($map[$humans[0]]['nickName'])->toBe('batch-user-1')
        ->and($map[$assistants[0]]['roles'])->toBe(['ai']);
});

it('skips ai_models entirely when every uuid is a human', function () {
    $humans = collect(range(1, 4))->map(fn ($i) => makeStudio("human-only-{$i}"))->all();

    DB::enableQueryLog();
    DB::flushQueryLog();
    app(UserService::class)->byUuids($humans);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries)->toBe(1);
});

it('remembers resolved identities within the request', function () {
    $uid = makeStudio('memoized');
    $service = app(UserService::class);
    $service->byUuid($uid);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $again = $service->byUuid($uid);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries)->toBe(0)
        ->and($again['nickName'])->toBe('memoized');
});

it('remembers negative results too', function () {
    $orphan = (string) Str::uuid();
    $service = app(UserService::class);
    $service->byUuid($orphan);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $service->byUuid($orphan);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries)->toBe(0);
});

it('re-reads after flush', function () {
    $uid = makeStudio('flushed');
    $service = app(UserService::class);
    $service->byUuid($uid);
    $service->flush();

    DB::enableQueryLog();
    DB::flushQueryLog();
    $service->byUuid($uid);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries)->toBeGreaterThan(0);
});

it('keeps input order and drops misses in the positional list', function () {
    $a = makeStudio('list-a');
    $b = makeStudio('list-b');
    $orphan = (string) Str::uuid();

    $list = app(UserService::class)->listByUuids([$b, $orphan, $a]);

    expect($list)->toHaveCount(2)
        ->and($list[0]['nickName'])->toBe('list-b')
        ->and($list[1]['nickName'])->toBe('list-a');
});

it('resolves either candidate through actor()', function () {
    $service = app(UserService::class);
    $uid = makeStudio('either-human');
    $human = UserInfo::where('userid', $uid)->first();
    $assistant = makeAssistant('either-ai');

    // 人类优先，AI 兜底，都没有给占位
    expect($service->actor($human, $assistant)['nickName'])->toBe('either-human')
        ->and($service->actor(null, $assistant)['nickName'])->toBe('either-ai')
        ->and($service->actor(null, null)['nickName'])->toBe('unknown');
});

it('always includes the matching column in the eager load spec', function () {
    [$human, $assistant] = UserService::eagerLoadActor();

    // 漏掉匹配列不会报错，只会让整列静默变 null——这条守的就是那个
    expect($human)->toStartWith('user:')
        ->and(explode(',', explode(':', $human)[1]))->toContain('userid')
        ->and($assistant)->toStartWith('aiModel:')
        ->and(explode(',', explode(':', $assistant)[1]))->toContain('uid');

    // 关系名可换，列白名单不跟着走样
    [$editor] = UserService::eagerLoadActor('editor', 'editorAssistant');
    expect($editor)->toStartWith('editor:')
        ->and(explode(',', explode(':', $editor)[1]))->toContain('userid');
});

it('forwards UserApi to the service unchanged', function () {
    $uid = makeStudio('forwarded');
    $service = app(UserService::class);

    expect(UserApi::getByUuid($uid))->toBe($service->byUuid($uid))
        ->and(UserApi::getIdByName('forwarded'))->toBe($uid)
        ->and(UserApi::getIdByUuid($uid))->toBe($service->intIdByUuid($uid))
        ->and(UserApi::getIntIdByName('forwarded'))->toBe($service->intIdByName('forwarded'))
        ->and(UserApi::getByName('forwarded'))->toBe($service->byName('forwarded'))
        ->and(UserApi::userInfo(null))->toBe($service->profile(null));
});

it('keeps the v2 quirk of returning null for a non-array list', function () {
    expect(UserApi::getListByUuid(null))->toBeNull()
        ->and(UserApi::getListByUuid('not-an-array'))->toBeNull()
        ->and(UserApi::getListByUuid([]))->toBeNull();
});

it('forwards AiAssistantApi to the service unchanged', function () {
    $assistant = makeAssistant('forwarded-ai');
    $service = app(UserService::class);

    expect(AiAssistantApi::getByUuid($assistant->uid))->toBe($service->assistantByUuid($assistant->uid))
        ->and(AiAssistantApi::userInfo($assistant))->toBe($service->assistantProfile($assistant))
        ->and(AiAssistantApi::userInfo(null))->toBe($service->assistantProfile(null));
});

it('picks a built-in logo for an assistant without an avatar', function () {
    $assistant = makeAssistant('logo-test', ['model' => 'gpt-4', 'url' => null]);

    $avatar = app(UserService::class)->assistantProfile($assistant)['avatar'];

    expect($avatar)->toStartWith(config('app.url').'/assets/images/avatar/')
        ->and($avatar)->toEndWith('.png');
});
