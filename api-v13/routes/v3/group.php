<?php

/*
|--------------------------------------------------------------------------
| v3 · group —— 团队（界面上叫「团队」，团队空间以它为 owner）
|--------------------------------------------------------------------------
|
| 由 routes/api.php 的 v3 组 require 进来，前缀已在那边加好。
| 资源名沿用表名与 v2 的 group，不改叫 teams（2026-11-13 定）。
|
*/

use App\Http\Controllers\V3\GroupController;
use App\Http\Controllers\V3\GroupMemberController;
use App\Http\Controllers\V3\GroupShareController;
use App\Http\Controllers\V3\MeGroupController;
use Illuminate\Support\Facades\Route;

// 我加入的团队：没有第二个主体（不会出现 me 以外的值），所以是 /me/ 下的独立端点
Route::get('me/groups', MeGroupController::class)->middleware('auth.v3')->name('me.groups.index');

// 团队本身。详情可不登录（公开团队谁都能看），其余要登录。只注册 PATCH，不注册 PUT
Route::apiResource('groups', GroupController::class)
    ->except(['update'])
    ->middlewareFor(['index', 'store', 'destroy'], 'auth.v3')
    ->whereUuid('group');
Route::patch('groups/{group}', [GroupController::class, 'update'])
    ->name('groups.update')
    ->middleware('auth.v3')
    ->whereUuid('group');

// 团队成员。scopeBindings：{member} 必须属于 {group}，否则 404。
// FIXME: {member} 现在是 group_members.id（雪花 id，内部主键不该进 URL）。一个用户在一个团队里只会有一行
//   （库里有唯一索引 group_members_user_id_group_id_unique），应改成 groups/{group}/members/{user_id}（用户 uuid），
//   按 (group_id, user_id) 定位；同时去掉 me/groups 的 member_id、成员行的 id 改成用户 uuid，
//   v6 的 src/api/group-member.ts 与 features/teamspace/refreshMyGroups.ts 跟着改。现在不改（2026-11-17 用户定）。
// 用组而不是 apiResource()->scoped()：后者会把 URI 写成 {group:}，规格生成器认不出。
// 只注册 PATCH，不注册 PUT（v3-resource skill 硬规范第 10 条）
Route::middleware('auth.v3')->scopeBindings()->group(function () {
    Route::apiResource('groups.members', GroupMemberController::class)
        ->only(['index', 'store', 'destroy'])
        ->whereUuid('group')
        ->whereNumber('member');
    Route::patch('groups/{group}/members/{member}', [GroupMemberController::class, 'update'])
        ->name('groups.members.update')
        ->whereUuid('group')
        ->whereNumber('member');

    // 团队文档（共享给团队的资源）。{share} 必须是共享给 {group} 的
    // FIXME: {share} 现在是 shares.id（雪花 id，内部主键不该进 URL）。在一个团队下，一项资源只会共享一次
    //   （库里有唯一索引 shares_res_id_res_type_cooperator_id_cooperator_type_unique，cooperator 就是团队），
    //   应改成 groups/{group}/shares/{res_id}（被共享资源的 uuid），按 (cooperator_id = group, res_id) 定位；
    //   资源 uuid 跨类型实际不会重复，真要严格就再加 ?res_type=。列表行的 id 跟着改成资源 uuid，
    //   v6 的移出团队调用一并改。现在不改（2026-11-17 用户定）。
    Route::apiResource('groups.shares', GroupShareController::class)
        ->only(['index', 'destroy'])
        ->whereUuid('group')
        ->whereNumber('share');
});
