<?php

namespace App\Http\Api;

use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Models\UserInfo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class StudioApi
{
    public static function getIdByName($name)
    {
        /**
         * 获取 uuid
         */
        // TODO 改为studio table
        if (empty($name)) {
            return false;
        }
        $userInfo = UserInfo::where('username', $name)->first();
        if ($userInfo) {
            // group
            return $userInfo->userid;
        } else {
            $group = GroupInfo::where('uid', $name)->first();
            if ($group) {
                return $group->uid;
            } else {
                return false;
            }
        }
    }

    public static function getById($id)
    {
        // TODO 改为studio table
        if (empty($id)) {
            return false;
        }
        $userInfo = UserInfo::where('userid', $id)->first();
        if ($userInfo) {
            $data = [
                'id' => $id,
                'nickName' => $userInfo->nickname,
                'realName' => $userInfo->username,
                'studioName' => $userInfo->username,
            ];
            if (! empty($userInfo->role)) {
                $data['roles'] = json_decode($userInfo->role);
            }
            if ($userInfo->avatar) {
                $img = str_replace('.jpg', '_s.jpg', $userInfo->avatar);

                if (App::environment(['local', 'testing'])) {
                    $data['avatar'] = Storage::url($img);
                } else {
                    $data['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
                }
            }

            return $data;
        } else {
            $group = GroupInfo::where('uid', $id)->first();
            if ($group) {
                $data = [
                    'id' => $id,
                    'nickName' => $group->name,
                    'realName' => $group->uid,
                    'studioName' => $group->uid,
                ];
            } else {
                return false;
            }
        }
    }

    public static function getByIntId($id)
    {
        // TODO 改为studio table
        if (empty($id)) {
            return false;
        }
        $userInfo = UserInfo::where('id', $id)->first();
        if (! $userInfo) {
            return false;
        }

        return [
            'id' => $userInfo['userid'],
            'nickName' => $userInfo['nickname'],
            'realName' => $userInfo['username'],
            'studioName' => $userInfo['username'],
            'avatar' => '',
        ];
    }

    public static function userCanManage($userId, $studioId)
    {
        if ($userId === $studioId) {
            return true;
        }
        $group = GroupMember::where('user_id', $userId)
            ->where('group_id', $studioId)
            ->first();
        if ($group && $group->power <= 1) {
            return true;
        }

        return false;
    }

    public static function userCanList($userId, $studioId)
    {
        if ($userId === $studioId) {
            return true;
        }
        $group = GroupMember::where('user_id', $userId)
            ->where('group_id', $studioId)
            ->first();
        if ($group && $group->power <= 2) {
            return true;
        }

        return false;
    }
}
