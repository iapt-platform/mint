<?php
namespace App\Http\Api;
use App\Models\DictInfo;

class DictApi{
    public static function getSysDict($name){
        $dict_info=  DictInfo::where('name',$name)
                    ->where('owner_id',config("app.admin.root_uuid"))
                    ->first();
        if(!$dict_info){
            return false;
        }else{
            return $dict_info->id;
        }
    }
}
