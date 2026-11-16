<?php

use \WikipaliApi\Api\GroupsApi;
use \WikipaliApi\Model\PatchApiV3GroupsGroupMembersMemberRequest;
use \WikipaliApi\Model\PatchApiV3GroupsGroupRequest;
use \WikipaliApi\Model\PostApiV3GroupsGroupMembersRequest;
use \WikipaliApi\Model\PostApiV3GroupsRequest;

/*
 * v3 团队：/v3/groups（搜索、建、看、改、删）、/v3/me/groups、成员、团队文档。
 * 固定的测试团队只在测试账号名下没有时建一次，跑完保留；「建 → 改 → 删」那一轮用临时团队。
 */

/** 测试账号名下的测试团队 uid：没有就建一个 */
function test_group(string $token): string
{
    $name = fixtures()['group_name'];
    $find = function () use ($token, $name): ?string {
        $res = request(fn () => api(GroupsApi::class, $token)->getApiV3MeGroupsWithHttpInfo(per_page: 200));
        assert_status(200, $res, 'me/groups');
        foreach ($res['body']['data'] as $group) {
            if ($group['name'] === $name && $group['role'] === 'owner') {
                return $group['id'];
            }
        }

        return null;
    };
    if ($id = $find()) {
        return $id;
    }
    $body = new PostApiV3GroupsRequest();
    $body->setName($name);
    $res = request(fn () => api(GroupsApi::class, $token)->postApiV3GroupsWithHttpInfo($body));
    assert_status(201, $res, '建测试团队');

    return $res['body']['data']['id'];
}

/** 第二个账号的 uuid（经 v2 用户搜索） */
function second_user(string $token): ?string
{
    $username = fixtures()['group_member_username'];
    if (! $username) {
        return null;
    }
    $res = raw('GET', '/v2/user', ['view' => 'key', 'key' => $username], null, $token);
    foreach ($res['body']['data']['rows'] ?? [] as $row) {
        if (($row['userName'] ?? null) === $username) {
            return $row['id'];
        }
    }

    return null;
}

/* ---- 无 token → 401 ---- */

test('GET /v3/me/groups（无 token）→ 401', function () {
    $res = request(fn () => api(GroupsApi::class)->getApiV3MeGroupsWithHttpInfo());
    assert_status(401, $res, 'me/groups 无 token');
    assert_problem($res, 'me/groups 无 token');
});

test('GET /v3/groups/{group}/members（无 token）→ 401', function () {
    $res = request(fn () => api(GroupsApi::class)->getApiV3GroupsGroupMembersWithHttpInfo('11111111-1111-4111-8111-111111111111'));
    assert_status(401, $res, 'members 无 token');
    assert_problem($res, 'members 无 token');
});

test('POST /v3/groups/{group}/members（无 token）→ 401', function () {
    $body = new PostApiV3GroupsGroupMembersRequest();
    $body->setUserId('11111111-1111-4111-8111-111111111111');
    $res = request(fn () => api(GroupsApi::class)->postApiV3GroupsGroupMembersWithHttpInfo('11111111-1111-4111-8111-111111111111', $body));
    assert_status(401, $res, 'members POST 无 token');
    assert_problem($res, 'members POST 无 token');
});

/* ---- 带 token ---- */

test('GET /v3/me/groups → 200，带我的角色', function () {
    $token = required_token();
    $group = test_group($token);
    $res = request(fn () => api(GroupsApi::class, $token)->getApiV3MeGroupsWithHttpInfo());
    assert_status(200, $res, 'me/groups');
    assert_has_key('meta', $res['body'], 'me/groups');
    $mine = array_values(array_filter($res['body']['data'], fn ($g) => $g['id'] === $group))[0] ?? null;
    assert_eq('owner', $mine['role'] ?? null, 'me/groups 测试团队的角色');
    assert_has_key('member_id', $mine, 'me/groups.data[]');
});

test('GET /v3/groups/{group}/members → 200；不存在的团队 → 404', function () {
    $token = required_token();
    $group = test_group($token);
    $res = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupMembersWithHttpInfo($group));
    assert_status(200, $res, 'members');
    $first = $res['body']['data'][0] ?? null;
    foreach (['id', 'group_id', 'role', 'joined_at', 'user'] as $key) {
        assert_has_key($key, $first, 'members.data[0]');
    }
    assert_eq('owner', $first['role'], 'members 第一行是 owner');

    $missing = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupMembersWithHttpInfo('11111111-1111-4111-8111-111111111111'));
    assert_status(404, $missing, 'members 团队不存在');
});

test('POST /v3/groups/{group}/members（缺 user_id）→ 422', function () {
    $token = required_token();
    $group = test_group($token);
    $res = raw('POST', "/v3/groups/{$group}/members", null, ['role' => 'reader'], $token);
    assert_status(422, $res, 'members 缺 user_id');
    assert_field_error('user_id', $res, 'members 缺 user_id');
});

test('groups 成员一轮：加人 201 → 改角色 200 → 改 owner 403 → 移人 204 → 再移 404', function () {
    $token = required_token();
    $group = test_group($token);
    $userId = second_user($token) ?? skip('fixtures.group_member_username 对应的账号不存在');

    // 上次中断可能留下这个人，先清掉
    $list = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupMembersWithHttpInfo($group, per_page: 200));
    foreach ($list['body']['data'] as $row) {
        if (($row['user']['id'] ?? null) === $userId) {
            raw('DELETE', "/v3/groups/{$group}/members/{$row['id']}", null, null, $token);
        }
    }

    $body = new PostApiV3GroupsGroupMembersRequest();
    $body->setUserId($userId);
    $add = request(fn () => api(GroupsApi::class, $token)->postApiV3GroupsGroupMembersWithHttpInfo($group, $body));
    assert_status(201, $add, '加人');
    assert_eq('reader', $add['body']['data']['role'] ?? null, '加人默认角色');
    $member = $add['body']['data']['id'];

    $patch = new PatchApiV3GroupsGroupMembersMemberRequest();
    $patch->setRole('editor');
    $update = request(fn () => api(GroupsApi::class, $token)->patchApiV3GroupsGroupMembersMemberWithHttpInfo($group, $member, $patch));
    assert_status(200, $update, '改角色');
    assert_eq('editor', $update['body']['data']['role'] ?? null, '改角色后');

    $ownerRow = $list['body']['data'][0]['id'];
    $patchOwner = request(fn () => api(GroupsApi::class, $token)->patchApiV3GroupsGroupMembersMemberWithHttpInfo($group, $ownerRow, $patch));
    assert_status(403, $patchOwner, '改 owner');
    assert_problem($patchOwner, '改 owner');

    $remove = request(fn () => api(GroupsApi::class, $token)->deleteApiV3GroupsGroupMembersMemberWithHttpInfo($group, $member));
    assert_status(204, $remove, '移人');

    $again = request(fn () => api(GroupsApi::class, $token)->deleteApiV3GroupsGroupMembersMemberWithHttpInfo($group, $member));
    assert_status(404, $again, '再移');
});

/* ---- 团队本身 ---- */

test('GET /v3/groups（无 token）→ 401', function () {
    $res = request(fn () => api(GroupsApi::class)->getApiV3GroupsWithHttpInfo());
    assert_status(401, $res, 'groups 无 token');
    assert_problem($res, 'groups 无 token');
});

test('GET /v3/groups?search= → 200，只列公开团队', function () {
    $token = required_token();
    test_group($token);
    $res = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsWithHttpInfo(search: fixtures()['group_name']));
    assert_status(200, $res, 'groups 搜索');
    assert_has_key('meta', $res['body'], 'groups 搜索');
    foreach ($res['body']['data'] as $group) {
        assert_eq(30, $group['status'], 'groups 搜索结果都是公开团队');
    }
});

test('GET /v3/groups/{group} → 200 带我的角色；不存在 → 404', function () {
    $token = required_token();
    $group = test_group($token);
    $res = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupWithHttpInfo($group));
    assert_status(200, $res, 'group 详情');
    assert_eq('owner', $res['body']['data']['role'] ?? null, 'group 详情里我的角色');

    $missing = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupWithHttpInfo('11111111-1111-4111-8111-111111111111'));
    assert_status(404, $missing, 'group 不存在');
});

test('POST /v3/groups（缺 name）→ 422；（无 token）→ 401', function () {
    $token = required_token();
    $res = raw('POST', '/v3/groups', null, ['description' => 'x'], $token);
    assert_status(422, $res, 'groups 缺 name');
    assert_field_error('name', $res, 'groups 缺 name');

    $anon = raw('POST', '/v3/groups', null, ['name' => 'x']);
    assert_status(401, $anon, 'groups POST 无 token');
});

test('groups 一轮：建 201 → 改 200 → 删 204 → 再看 404', function () {
    $token = required_token();
    $body = new PostApiV3GroupsRequest();
    $body->setName('e2e-tmp-'.time());
    $create = request(fn () => api(GroupsApi::class, $token)->postApiV3GroupsWithHttpInfo($body));
    assert_status(201, $create, '建团队');
    assert_eq('owner', $create['body']['data']['role'] ?? null, '建团队后我的角色');
    $group = $create['body']['data']['id'];

    $patch = new PatchApiV3GroupsGroupRequest();
    $patch->setDescription('api-test');
    $patch->setStatus(10);
    $update = request(fn () => api(GroupsApi::class, $token)->patchApiV3GroupsGroupWithHttpInfo($group, $patch));
    assert_status(200, $update, '改团队');
    assert_eq(10, $update['body']['data']['status'] ?? null, '改团队后的状态');

    $delete = request(fn () => api(GroupsApi::class, $token)->deleteApiV3GroupsGroupWithHttpInfo($group));
    assert_status(204, $delete, '删团队');

    $gone = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupWithHttpInfo($group));
    assert_status(404, $gone, '删后再看');
});

/* ---- 团队文档 ---- */

test('GET /v3/groups/{group}/shares → 200；（无 token）→ 401', function () {
    $token = required_token();
    $group = test_group($token);
    $res = request(fn () => api(GroupsApi::class, $token)->getApiV3GroupsGroupSharesWithHttpInfo($group));
    assert_status(200, $res, 'group shares');
    assert_has_key('meta', $res['body'], 'group shares');

    $anon = request(fn () => api(GroupsApi::class)->getApiV3GroupsGroupSharesWithHttpInfo($group));
    assert_status(401, $anon, 'group shares 无 token');
});
