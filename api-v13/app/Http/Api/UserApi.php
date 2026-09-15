<?php

namespace App\Http\Api;

use App\Models\AiModel;
use App\Models\UserInfo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class UserApi
{
    public static function getIdByName($name)
    {
        return UserInfo::where('username', $name)->value('userid');
    }

    public static function getIdByUuid($uuid)
    {
        return UserInfo::where('userid', $uuid)->value('id');
    }

    public static function getIntIdByName($name)
    {
        return UserInfo::where('username', $name)->value('id');
    }

    public static function getById($id)
    {
        $user = UserInfo::where('id', $id)->first();

        return UserApi::userInfo($user);
    }

    public static function getByName($name)
    {
        $user = UserInfo::where('username', $name)->first();

        return UserApi::userInfo($user);
    }

    public static function getByUuid($id)
    {
        $user = UserInfo::where('userid', $id)->first();
        if (! $user) {
            return AiAssistantApi::getByUuid($id);
        }

        return UserApi::userInfo($user);
    }

    public static function getListByUuid($uuid)
    {
        if (! $uuid || ! is_array($uuid)) {
            return null;
        }
        $users = UserInfo::whereIn('userid', $uuid)->get();
        $assistants = AiModel::whereIn('uid', $uuid)->get();
        $output = [];
        foreach ($uuid as $key => $id) {
            foreach ($users as $user) {
                if ($user->userid === $id) {
                    $output[] = UserApi::userInfo($user);

                    continue;
                }
            }
            foreach ($assistants as $assistant) {
                if ($assistant->uid === $id) {
                    $output[] = AiAssistantApi::userInfo($assistant);

                    continue;
                }
            }
        }

        return $output;
    }

    public static function userInfo($user)
    {
        if (! $user) {
            return [
                'id' => 0,
                'nickName' => 'unknown',
                'userName' => 'unknown',
                'realName' => 'unknown',
                'avatar' => '',
            ];
        }
        $data = [
            'id' => $user->userid,
            'nickName' => $user->nickname,
            'userName' => $user->username,
            'realName' => $user->username,
            'sn' => $user->id,
        ];
        if (! empty($user->role)) {
            $data['roles'] = json_decode($user->role);
        }
        if ($user->avatar) {
            $img = str_replace('.jpg', '_s.jpg', $user->avatar);
            if (App::environment(['local', 'testing'])) {
                $data['avatar'] = Storage::url($img);
            } else {
                $data['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
            }
        }

        return $data;
    }
}
