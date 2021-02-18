<?php
/*
*
list (doc_id)
add (doc_id ,userid)
del (doc_id, userid)
set (doc_id ,userid ,value)
*
*/
    require_once "../path.php";
    require_once "../public/_pdo.php";
    require_once "../public/function.php";
    require_once "../ucenter/function.php";

    
    $userid="";
    $isLogin=false;
    if($_COOKIE["userid"]){
        $userid=$_COOKIE["userid"];
        $isLogin = true;
    }
    if($_GET["do"]){
        $_do=$_GET["do"];
    }
    else{
        echo "Error:缺乏必要的参数 do";
        exit;
    }
    if($_GET["doc_id"]){
        $_doc_id=$_GET["doc_id"];
    }
    else{
        echo "Error:缺乏必要的参数 doc_id";
        exit;
    } 

    $powerlist["10"] = "阅读";
    $powerlist["20"] = "建议";
    $powerlist["30"] = "修改";
    $powerlist["40"] = "管理员";

    PDO_Connect("sqlite:"._FILE_DB_FILEINDEX_);

        echo "<input id='doc_coop_docid' type='hidden' value='{$_doc_id}' />";
        $query = "SELECT * from fileindex where id = ? ";
        $Fetch = PDO_FetchAll($query,array($_doc_id));
        $iFetch=count($Fetch);
        if($iFetch>0){

            $owner = $Fetch[0]["user_id"];
            $uid = $_COOKIE["uid"];
            if($owner==$uid){
                //自己的文档
                switch($_do){
                    case "list":
                    break;
                    case "add":
                        $query="INSERT INTO power ('id','doc_id','user','power','status','create_time','modify_time','receive_time') 
                        VALUES (?,?,?,?,?,?,?,?)";
                        $stmt = $PDO->prepare($query);
                        $stmt->execute( 
                            array(UUID::v4(),
                            $_GET["doc_id"],
                            $_GET["user_id"],
                            10,
                            1,
                            mTime(),
                            mTime(),
                            mTime())
                        );
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            echo "error - $error[2] <br>";
                        }
                    break;
                    case "del":
                        $query="DELETE FROM power WHERE doc_id = ? AND user = ? ";
                        $stmt = $PDO->prepare($query);
                        $stmt->execute( 
                            array($_GET["doc_id"],
                            $_GET["user_id"])
                        );
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            echo "error - $error[2] <br>";
                        }
                    break;
                    case "set":
                        $query="UPDATE power SET power = ? , modify_time = ? WHERE doc_id = ? AND user = ? ";
                        $stmt = $PDO->prepare($query);
                        $stmt->execute( 
                            array($_GET["value"],
                            mTime(),
                            $_GET["doc_id"],
                            $_GET["user_id"])
                        );
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            echo "error - $error[2] <br>";
                        }
                    break;

                }
                
                $query = "SELECT * from power where doc_id = ? ";
                $Fetch = PDO_FetchAll($query,array($_doc_id));

                echo "<ul>";
                foreach($Fetch as $row){
                    echo "<li>";
                    echo ucenter_getA($row["user"],"username");
                    echo "<select onchange=\"coop_power_change('{$row["user"]}',this)\">";
                    foreach($powerlist as $key=>$value){
                        echo "<option value='{$key}' ";
                        if($row["power"]==$key){
                            echo "selected";
                        }
                        echo ">{$value}</option>";
                    }
                    echo "</select>";
                    echo "<button onclick=\"coop_del('{$row["user"]}')\">删除</button>";
                    echo "</li>";
                }
                echo "</ul>";
                ?>
                添加协作者
                <div id="wiki_search" style="width:100%;">
                    <div><input id="username_input" type="input" placeholder="用户名" onkeyup="username_search_keyup(event,this)"/></div>
                    <div id="search_result">
                    </div>
			    </div>
                <?php
            }
            else{
                 //别人的的文档
                 echo "<a href='fork.php?doc_id={$doc_id}'>[复刻]</a>";
                
            }
        }



?>
