<?php

namespace App\Http\Api;

use App\Models\AiModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class AiAssistantApi
{
    public static function getByUuid($id)
    {
        $user = AiModel::where('uid', $id)->first();

        return self::userInfo($user);
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
            'id' => $user->uid,
            'nickName' => $user->name,
            'userName' => $user->real_name,
            'realName' => $user->real_name,
            'roles' => ['ai'],
            'sn' => 0,
        ];

        if ($user->avatar) {
            $img = str_replace('.jpg', '_s.jpg', $user->avatar);
            if (App::environment('local')) {
                $data['avatar'] = Storage::url($img);
            } else {
                $data['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
            }
        } else {
            $logo = null;
            foreach (config('mint.ai.logo') as $key => $value) {
                // model / url 均可为 null（新建模型时未必填写），须转字符串避免 strpos 弃用告警
                if (strpos((string) $user->model, $key) !== false) {
                    $logo = $value;
                    break;
                } elseif (strpos((string) $user->url, $key) !== false) {
                    $logo = $value;
                    break;
                }
            }
            $base = config('app.url').'/assets/images/avatar/';
            if ($logo === null) {
                $data['avatar'] = $base.'ai-assistant.png';
            } else {
                $data['avatar'] = $base.$logo;
            }
        }

        return $data;
    }
}
