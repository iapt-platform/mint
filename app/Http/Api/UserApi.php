<?php
namespace App\Http\Api;

use App\Models\UserInfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class UserApi{
    public static function getIdByName($name){
        return UserInfo::where('username',$name)->value('userid');
    }
    public static function getIdByUuid($uuid){
        return UserInfo::where('userid',$uuid)->value('id');
    }
    public static function getIntIdByName($name){
        return UserInfo::where('username',$name)->value('id');
    }
    public static function getById($id){
        $user = UserInfo::where('id',$id)->first();
        if($user){
            return [
                'id'=>$id,
                'uid'=>$user->userid,
                'nickName'=>$user['nickname'],
                'userName'=>$user['username'],
                'realName'=>$user['username'],
                'avatar'=>'',
            ];
        }else{
            Log::error('$user=null;$id='.$id);
            return [
                'id'=>0,
                'nickName'=>'unknown',
                'userName'=>'unknown',
                'realName'=>'unknown',
                'avatar'=>'',
            ];
        }

    }
    public static function getByUuid($id){
        $user = UserInfo::where('userid',$id)->first();
        if($user){
            $data = [
                'id'=>$id,
                'nickName'=>$user['nickname'],
                'userName'=>$user['username'],
                'realName'=>$user['username'],
            ];
            if(!empty($user->role)){
                $data['roles'] = json_decode($user->role);
            }
            if($user->avatar){
                $img = str_replace('.jpg','_s.jpg',$user->avatar);
                if (App::environment('local')) {
                    $data['avatar'] = Storage::url($img);
                }else{
                    $data['avatar'] = Storage::temporaryUrl($img, now()->addDays(6));
                }
            }
            return $data;
        }else{
            Log::error('$user=null;$id='.$id);
            return [
                'id'=>0,
                'nickName'=>'unknown',
                'userName'=>'unknown',
                'realName'=>'unknown',
                'avatar'=>'',
            ];
        }

    }
}
