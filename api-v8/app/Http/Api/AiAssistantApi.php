<?php

namespace App\Http\Api;

use App\Models\AiModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class AiAssistantApi
{
    public static function getByUuid($id)
    {
        $user = AiModel::where('uid', $id)->first();
        return self::userInfo($user);
    }
    public static function userInfo($user)
    {
        if (!$user) {
            Log::warning('$user=null;');
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
                if (strpos($user->model, $key) !== false) {
                    $logo = $value;
                    break;
                } else if (strpos($user->url, $key) !== false) {
                    $logo = $value;
                    break;
                }
            }
            $base = config('app.url') . '/assets/images/avatar/';
            if ($logo === null) {
                $data['avatar'] = $base . 'ai-assistant.png';
            } else {
                $data['avatar'] = $base . $logo;
            }
        }
        return $data;
    }
}
