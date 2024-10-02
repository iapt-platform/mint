<?php
namespace App\Http\Api;
use App\Models\GroupMember;
use App\Models\Share;
use App\Models\Article;
use App\Models\Channel;
use App\Models\Collection;
use App\Http\Api\ChannelApi;

class ShareApi{

    /**
     * 获取某用户的可见的协作资源
     * $res_type 见readme.md#资源类型 -1全部类型资源
     * ## 资源类型
     *  1 PCS 文档
     *  2 Channel 版本
     *  3 Article 文章
     *  4 Collection 文集
     *  5 版本片段
     * power 权限 10: 只读  20：编辑 30： 拥有者
     */

    public static function getResList($user_uid,$res_type=-1){
        # 找我加入的群
        $my_group = GroupMember::where("user_id",$user_uid)->select('group_id')->get();
        $userList[] = $user_uid;
        foreach ($my_group as $key => $value) {
            # code...
            $userList[]=$value["group_id"];
        }

        if($res_type==-1){
            #所有类型资源
            $Fetch =Share::whereIn("cooperator_id",$userList)->select(['res_id','res_type','power'])->get();
        }
        else{
            #指定类型资源
            $Fetch =Share::whereIn("cooperator_id",$userList)
                        ->where('res_type',$res_type)
                        ->select(['res_id','res_type','power'])->get();
        }

        $resOutput = array();
        foreach ($Fetch as $key => $value) {
            # 查重
            if(isset($resOutput[$value["res_id"]])){
                if($value["power"]>$resOutput[$value["res_id"]]["power"]){
                    $resOutput[$value["res_id"]]["power"] = $value["power"];
                }
            }
            else{
                $resOutput[$value["res_id"]]= array("power"=> $value["power"],"type" => $value["res_type"]);
            }
        }
        $resList=array();
        foreach ($resOutput as $key => $value) {
            # code...
            $resList[]=array("res_id"=>$key,"res_type"=>(int)$value["type"],"power"=>(int)$value["power"]);
        }

        foreach ($resList as $key => $res) {
            # 获取资源标题 和所有者
            $resList[$key]["res_title"]="_unknown_";
            $resList[$key]["res_owner_id"]="_unknown_";
            $resList[$key]["type"]="_unknown_";
            $resList[$key]["status"]="0";
            $resList[$key]["lang"]="_unknown_";

            switch ($res["res_type"]) {
                case 1:
                    # pcs 文档
                    $resList[$key]["res_title"]="title";
                    break;
                case 2:
                    # channel
                    $channelInfo = Channel::where('uid',$res["res_id"])->first();
                    if($channelInfo){
                        $resList[$key]["res_title"]=$channelInfo["name"];
                        $resList[$key]["res_owner_id"]=$channelInfo["owner_uid"];
                        $resList[$key]["type"]=$channelInfo["type"];
                        $resList[$key]["status"]=$channelInfo["status"];
                        $resList[$key]["lang"]=$channelInfo["lang"];
                    }
                    break;
                case 3:
                    # 3 Article 文章
                    $aInfo = Article::where('uid',$res["res_id"])->first();
                    if($aInfo){
                        $resList[$key]["res_title"]=$aInfo["title"];
                        $resList[$key]["res_owner_id"]=$aInfo["owner"];
                        $resList[$key]["status"]=$aInfo["status"];
                        $resList[$key]["lang"]='';
                    }
                    break;
                case 4:
                    # 4 Collection 文集
                    $aInfo = Collection::where('uid',$res["res_id"])->first();
                    if($aInfo){
                        $resList[$key]["res_title"]=$aInfo["title"];
                        $resList[$key]["res_owner_id"]=$aInfo["owner"];
                        $resList[$key]["status"]=$aInfo["status"];
                        $resList[$key]["lang"]=$aInfo["lang"];
                    }
                    break;
                case 5:
                    # code...
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $resList;

    }

    /**
     * 获取对某个共享资源的权限
     */
    public static function getResPower($user_uid,$res_id,$res_type=0){
            if(empty($user_uid)){
                #未登录用户 没有共享资源
                return 0;
            }
            //查看是否为资源拥有者
            if($res_type!=0){
                switch ($res_type) {
                    case 2:
                        # channel
                        $channel = ChannelApi::getById($res_id);
                        if($channel){
                            if($channel['studio_id'] === $user_uid){
                                return 30;
                            }
                        }
                        break;
                    case 3:
                        //Article
                        $owner = Article::where('uid',$res_id)->value('owner');
                        if($owner === $user_uid){
                            return 30;
                        }
                        break;
                    case 4:
                        $owner = Collection::where('uid',$res_id)->value('owner');
                        if($owner === $user_uid){
                            return 30;
                        }
                        //文集
                        break;
                }
            }
            # 找我加入的群
            $my_group = GroupMember::where("user_id",$user_uid)->select('group_id')->get();
            $userList[] = $user_uid;
            foreach ($my_group as $key => $value) {
                $userList[]=$value["group_id"];
            }
            $Fetch =Share::whereIn("cooperator_id",$userList)
                        ->where('res_id',$res_id)
                        ->select(['power'])->get();
            $power=0;
            foreach ($Fetch as $key => $value) {
                # code...
                if((int)$value["power"]>$power){
                    $power = $value["power"];
                }
            }
            return $power;
    }

}

