<?php

namespace App\Http\Api;

use App\Services\UserService;

/**
 * **冻结的转发壳，不要往里写逻辑。**
 *
 * 实现全部在 {@see UserService}。保留这个类只为一件事：让 v2 的六十多个调用点
 * 不必改动——改它们要配快照测试，而 dashboard-v4 在线上跑着且无人维护。
 *
 * 新代码一律注入 `UserService`。等本类的调用点清零，连同 `app/Http/Api/` 一起删。
 *
 * 方法名沿用 v2 的旧名，其中几个名不副实（`getIdByName` 返回的是 uuid，
 * `getIdByUuid` 返回的是自增主键），`UserService` 那边已改成诚实的名字。
 *
 * @deprecated 用 App\Services\UserService
 */
class UserApi
{
    /** @deprecated 用 UserService::uuidByName() */
    public static function getIdByName($name)
    {
        return self::service()->uuidByName($name);
    }

    /** @deprecated 用 UserService::intIdByUuid() */
    public static function getIdByUuid($uuid)
    {
        return self::service()->intIdByUuid($uuid);
    }

    /** @deprecated 用 UserService::intIdByName() */
    public static function getIntIdByName($name)
    {
        return self::service()->intIdByName($name);
    }

    /** @deprecated 用 UserService::byId() */
    public static function getById($id)
    {
        return self::service()->byId($id);
    }

    /** @deprecated 用 UserService::byName() */
    public static function getByName($name)
    {
        return self::service()->byName($name);
    }

    /** @deprecated 用 UserService::byUuid()；列表场景用 byUuids()，别循环调这个 */
    public static function getByUuid($id)
    {
        return self::service()->byUuid($id);
    }

    /** @deprecated 用 UserService::byUuids()，它返回 uuid => 摘要的映射，不会丢项 */
    public static function getListByUuid($uuid)
    {
        // 非数组返回 null 是 v2 的既有口径，留在适配层，不要带进 Service
        if (! $uuid || ! is_array($uuid)) {
            return null;
        }

        return self::service()->listByUuids($uuid);
    }

    /** @deprecated 用 UserService::profile() */
    public static function userInfo($user)
    {
        return self::service()->profile($user);
    }

    private static function service(): UserService
    {
        return app(UserService::class);
    }
}
