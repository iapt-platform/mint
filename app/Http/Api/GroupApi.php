<?php
namespace App\Http\Api;
use App\Models\GroupInfo;

class GroupApi{
    public static function getById($id){
        $group = GroupInfo::where("uid",$id)->first();
        if($group){
            return [
                    'id'=>$id,
                    'name'=>$group->name,
                ];
        }else{
            return false;
        }
    }

}
