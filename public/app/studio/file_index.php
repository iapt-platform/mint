<?php
require_once 'checklogin.inc';
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

if (isset($_POST["op"])) {
    $op = $_POST["op"];
}
if (isset($_POST["id"])) {
    $id = $_POST["id"];
}
if (isset($_POST["filename"])) {
    $filename = $_POST["filename"];
}
if (isset($_POST["doc_id"])) {
    $doc_id = $_POST["doc_id"];
}
if (isset($_POST["field"])) {
    $field = $_POST["field"];
}
if (isset($_POST["value"])) {
    $value = $_POST["value"];
}
if ($_COOKIE["uid"]) {
    $uid = $_COOKIE["uid"];
} else {
    echo "尚未登录";
    exit;
}

PDO_Connect( _FILE_DB_FILEINDEX_);

switch ($op) {
    case "list":
        break;
    case "get";
        $query = "SELECT * from "._TABLE_FILEINDEX_." where user_id=? AND  id=?";
        $Fetch = PDO_FetchAll($query,[$uid,$doc_id]);
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        break;
    case "getall";
        //
        $query = "SELECT * from "._TABLE_FILEINDEX_." where user_id=? AND  id=?";
        $Fetch = PDO_FetchAll($query,[$uid,$_POST["doc_id"]]);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo json_encode($Fetch[0], JSON_UNESCAPED_UNICODE);
        }
        break;
    case "set";
        //修改文件索引数据库
        if ($field == "accese_time") {
            $value = mTime();
        }
        $doc_id = $_POST["doc_id"];
        $query = "UPDATE "._TABLE_FILEINDEX_." SET $field='$value' where user_id=? AND  uid=?";
        $stmt = @PDO_Execute($query,array($uid,$doc_id));
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            echo json_encode(array("error" => $error[2], "message" => $query), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array("error" => false, "message" => $query), JSON_UNESCAPED_UNICODE);
        }
        break;
    case "share":
        //修改文件索引数据库
        if (isset($_POST["file"])) {
            if (isset($_POST["share"])) {
                $share = $_POST["share"];
            } else {
                $share = 0;
            }
            $fileList = $_POST["file"];
            $aFileList = str_getcsv($fileList);
            if (count($aFileList) > 0) {
                $strFileList = "(";
                foreach ($aFileList as $file) {
                    $strFileList .= "'{$file}',";
                }
                $strFileList = mb_substr($strFileList, 0, mb_strlen($strFileList, "UTF-8") - 1, "UTF-8");
                $strFileList .= ")";
                $query = "UPDATE "._TABLE_FILEINDEX_." SET share='$share' where user_id='$uid' AND   uid in $strFileList";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error:{$error[2]}";
                } else {
                    echo "ok" . $query;
                }
            }
        }
        break;

    case "delete": //移到回收站
        {
            if (isset($_POST["file"])) {
                $fileList = $_POST["file"];
                $aFileList = str_getcsv($fileList);
                if (count($aFileList) > 0) {
                    $strFileList = "(";
                    foreach ($aFileList as $file) {
                        $strFileList .= "'{$file}',";
                    }
                    $strFileList = mb_substr($strFileList, 0, mb_strlen($strFileList, "UTF-8") - 1, "UTF-8");
                    $strFileList .= ")";

                    $query = "UPDATE "._TABLE_FILEINDEX_." SET status='0',share='0' where user_id='$uid' AND  uid in $strFileList";
                    $stmt = @PDO_Execute($query);
                    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                        $error = PDO_ErrorInfo();
                        echo "error:{$error[2]}";
                    } else {
                        echo "ok";
                    }
                }
            }
            break;
        }
    case "restore": //从回收站中恢复
        if (isset($_POST["file"])) {
            $fileList = $_POST["file"];
            $aFileList = str_getcsv($fileList);
            if (count($aFileList) > 0) {
                $strFileList = "(";
                foreach ($aFileList as $file) {
                    $strFileList .= "'{$file}',";
                }
                $strFileList = mb_substr($strFileList, 0, mb_strlen($strFileList, "UTF-8") - 1, "UTF-8");
                $strFileList .= ")";

                $query = "UPDATE "._TABLE_FILEINDEX_." SET status='1' where user_id='$uid' AND  uid in $strFileList";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error:{$error[2]}";
                } else {
                    echo "ok";
                }
            }
        }
        break;
    case "remove":
        //彻底删除文件
        if (isset($_POST["file"])) {
            $fileList = $_POST["file"];
            $aFileList = str_getcsv($fileList);
            if (count($aFileList) > 0) {
                $strFileList = "(";
                //删除文件
                foreach ($aFileList as $file) {
                    if (!unlink($dir . $file)) {
                        echo ("Error deleting $file");
                    }
                    $strFileList .= "'{$file}',";
                }
                $strFileList = mb_substr($strFileList, 0, mb_strlen($strFileList, "UTF-8") - 1, "UTF-8");
                $strFileList .= ")";
                //删除记录
                $query = "DELETE FROM "._TABLE_FILEINDEX_." WHERE user_id='$uid' AND   uid in $strFileList";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error:{$error[2]}";
                } else {
                    echo "删除" . count($aFileList) . "个文件。";
                }
            }
        }
        break;
    case "remove_all":
        //    清空回收站
        break;
}
