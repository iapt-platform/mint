<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond = array("status" => 0, "message" => "");
if (isset($_COOKIE["userid"]) && isset($_POST["groupid"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    #TODO 先查是否有删除权限
    $query = "SELECT parent from group_info where id=? and owner=? ";
    $gInfo = PDO_FetchRow($query, array($_POST["groupid"], $_COOKIE["userid"]));
    if ($gInfo) {
        #删除group info
        $query = "DELETE from group_info where id=? and owner=? ";
        PDO_Execute($query, array($_POST["groupid"], $_COOKIE["userid"]));
        #删除 组员
        $query = "DELETE from group_member where group_id=? ";
        PDO_Execute($query, array($_POST["groupid"]));
        #删除到此组的分享

        #查询是否有子项目
        $query = "SELECT id from group_info where parent=? ";
        $project = PDO_FetchAll($query, array($_POST["groupid"]));
        if (count($project)) {
            $arrProject = array();
            foreach ($project as $key => $value) {
                # code...
                $arrProject[] = $value["id"];
            }
            $place_holders = implode(',', array_fill(0, count($arrProject), '?'));
            #删除 parent info
            $query = "DELETE from group_info where id IN ($place_holders) ";
            PDO_Execute($query, $arrProject);
            #删除 parent 组员
            $query = "DELETE from group_member where group_id IN ($place_holders) ";
            PDO_Execute($query, $arrProject);
            #删除到此组的分享
        }
    } else {
        $respond['status'] = 1;
        $respond['message'] = "no power to delete ";
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
