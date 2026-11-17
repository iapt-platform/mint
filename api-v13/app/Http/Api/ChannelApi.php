<?php

namespace App\Http\Api;

use App\Models\Channel;
use App\Services\ChannelService;
use App\Services\V3\ChannelAbilityService;

/**
 * **v2 专用，v3 代码一律不准引用本类。**
 *
 * 两部分：
 * - 纯能力（取 channel 摘要、系统 channel、自定义书 channel）：**冻结的转发壳**，实现全在
 *   {@see ChannelService}，新代码直接注入它。保留这些方法只为 v2 的几十个调用点不必改。
 * - 权限判定（`getCanReadByUser` … `canManageByUser`）：v2 的旧口径——只认拥有者本人 + 共享，
 *   不认团队角色、不排除软删以外的状态。**不转发到 v3**：v3 的口径（团队角色、status 20 公开不列出）
 *   在 {@see ChannelAbilityService}，把 v2 转过去等于改 v2 权限（铁律 1）。
 *   原地留着，随 v4 下线删除。
 *
 * @deprecated 纯能力用 App\Services\ChannelService；权限用 App\Services\V3\ChannelAbilityService
 */
class ChannelApi
{
    /** @deprecated 用 ChannelService::summary() */
    public static function getById(?string $id)
    {
        return self::service()->summary($id);
    }

    /** @deprecated 用 ChannelService::summaries() */
    public static function getByIds(array $ids)
    {
        return self::service()->summaries($ids);
    }

    /** v2 口径，v3 用 ChannelAbilityService::scopeListable() / abilities() */
    public static function getCanReadByUser($userUuid = null)
    {
        // 获取 user 在某章节 所有有权限的 channel 列表
        $channelId = [];
        // 我自己的

        if ($userUuid) {
            $my = Channel::select('uid')->where('owner_uid', $userUuid)->get();
            foreach ($my as $key => $value) {
                $channelId[$value->uid] = $value->uid;
            }

            // 获取共享channel

            $allSharedChannels = ShareApi::getResList($userUuid, 2);
            foreach ($allSharedChannels as $key => $value) {
                $channelId[$value['res_id']] = $value['res_id'];
            }
        }
        // 获取全网公开的channel
        $my = Channel::select('uid')->where('status', 30)->get();
        foreach ($my as $key => $value) {
            $channelId[$value->uid] = $value->uid;
        }
        $output = [];
        foreach ($channelId as $key => $value) {
            $output[] = $key;
        }

        return $output;
    }

    /** v2 口径，v3 用 ChannelAbilityService::writableUids() */
    public static function getCanEditByUser($userUuid = null)
    {
        // 获取 user 在某章节 所有有权限的 channel 列表
        $channelId = [];
        // 我自己的

        if ($userUuid) {
            $my = Channel::select('uid')->where('owner_uid', $userUuid)->get();
            foreach ($my as $key => $value) {
                $channelId[$value->uid] = $value->uid;
            }

            // 获取共享channel

            $allSharedChannels = ShareApi::getResList($userUuid, 2);
            foreach ($allSharedChannels as $key => $value) {
                if ($value['power'] >= 20) {
                    $channelId[$value['res_id']] = $value['res_id'];
                }
            }
        }

        $output = [];
        foreach ($channelId as $key => $value) {
            $output[] = $key;
        }

        return $output;
    }

    public static function userCanRead($userUid, $channelUid)
    {
        $channels = ChannelApi::getCanReadByUser($userUid);

        return in_array($channelUid, $channels);
    }

    /** v2 口径，v3 用 ChannelAbilityService::canWrite() */
    public static function userCanEdit($userUid, $channelUid)
    {
        $channels = ChannelApi::getCanEditByUser($userUid);

        return in_array($channelUid, $channels);
    }

    public static function canManageByUser($channelId, $userUuid)
    {
        $isOwner = Channel::where('owner_uid', $userUuid)
            ->where('uid', $channelId)->exists();

        return $isOwner;
    }

    /** @deprecated 用 ChannelService::systemUid() */
    public static function getSysChannel($channel_name, $fallback = '')
    {
        return self::service()->systemUid((string) $channel_name, (string) $fallback);
    }

    /** @deprecated 用 ChannelService::byNameOrFail() */
    public static function getChannelByName($name)
    {
        return self::service()->byNameOrFail((string) $name);
    }

    /**
     * 获取某个studio 的某个语言的自定义书的channel
     * 如果没有，建立
     *
     * @deprecated 用 ChannelService::userBookUid()
     */
    public static function userBookGetOrCreate($studioId, $lang, $status)
    {
        return self::service()->userBookUid((string) $studioId, (string) $lang, (int) $status);
    }

    private static function service(): ChannelService
    {
        return app(ChannelService::class);
    }
}
