<?php
//文集列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../collect/function.php';


    $onepage = 40;
    if(isset($_GET["page"])){
        $onepage = (int)$_GET["page"];
    }
    if(isset($_GET["begin"])){
        if($_GET["begin"]>=0){
            $begin=(int)$_GET["begin"];
        }
        else{
            echo json_encode(array("data"=>array(),"next"=>-1), JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    else{
        $begin = 0;
    }
    PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
    $query = "SELECT uid as id,title,subtitle,summary,owner,modify_time from "._TABLE_ARTICLE_."  where status >= 30 ";
    
    if(isset($_GET["orderby"])){
        switch ($_GET["orderby"]) {
            case 'like':
                # code...
                $query .="ORDER BY  DESC";
                break;
            
            default:
                # code...
                break;
        }
    }
    else{
        $query .="ORDER BY modify_time DESC";
    }
    $query .=" LIMIT $onepage OFFSET $begin  ";
    $Fetch = PDO_FetchAll($query);
    $collect_info = new CollectInfo();
    foreach ($Fetch as $key => $value) {
        # code...
        $userinfo = new UserInfo();
        $user = $userinfo->getName($value["owner"]);
        $Fetch[$key]["username"] = $user;
        $query = "SELECT collect_id from "._TABLE_ARTICLE_COLLECTION_."  where article_id = ? ";
        $collect = PDO_FetchRow($query,array($Fetch[$key]["id"]));
        if($collect){
            $Fetch[$key]["collect"] = $collect_info->get($collect["collect_id"]);
        }
        else{
            $Fetch[$key]["collect"] =FALSE;
        }
    }

    $output = array();
    $output["data"] =  $Fetch;
    if(count($Fetch)==$onepage){
        $output["next"] =  $begin+$onepage;
    }
    else{
        $output["next"] = -1;
    }

    echo json_encode($output, JSON_UNESCAPED_UNICODE);