<?php
header('Content-type: application/json; charset=utf8');

//查询term字典
require_once "../config.php";
require_once "../public/function.php";

$PDO = new PDO("" . _FILE_DB_SENTENCE_, "", "", array(PDO::ATTR_PERSISTENT => true));
$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if (isset($_GET["op"])) {
    $op = $_GET["op"];
} else if (isset($_POST["op"])) {
    $op = $_POST["op"];
}

switch ($op) {
    case "sync":
        {

            $time = $_POST["time"];
            $query = "select id as guid,modify_time from sentence  where receive_time>'{$time}' limit 0,10000";

            $stmt = $PDO->query($query);
            $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $iFetch = count($Fetch);
            echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

            break;
        }
    case "get":
        {
            if (isset($_GET["id"])) {
                $id = $_GET["id"];
            } else if (isset($_POST["id"])) {
                $id = $_POST["id"];
            } else {
                return;
            }
            $arrId = json_decode($id);
            $queryId = "('";
            foreach ($arrId as $one) {
                $queryId .= $one . "','";
            }
            $queryId = substr($queryId, 0, -2) . ")";
            $query = "SELECT
						id,
						block_id,
						book,
						paragraph,
						begin,
						end,
						tag,
						author,
						editor,
						text,
						language,
						ver,
						status,
						receive_time,
						modify_time
						FROM sentence
						WHERE id in {$queryId}";

            $stmt = $PDO->query($query);
            $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
            break;
        }
    case "insert":
        {
            echo "正在准备插入记录<br>";
            if (isset($_POST["data"])) {
                $data = $_POST["data"];
            } else {
                echo "没有数据<br>";
                return;
            }
            $arrData = json_decode($data);
            // 开始一个事务，关闭自动提交

            $PDO->beginTransaction();
            $query = "INSERT INTO sentence (
							'id',
							'block_id',
							'book',
							'paragraph',
							'begin',
							'end',
							'tag',
							'author',
							'editor',
							'text',
							'language',
							'ver',
							'status',
							'receive_time',
							'modify_time'
							) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $PDO->prepare($query);
            foreach ($arrData as $oneParam) {
                $param = array();
                $param[] = $oneParam->id;
                $param[] = $oneParam->block_id;
                $param[] = $oneParam->book;
                $param[] = $oneParam->paragraph;
                $param[] = $oneParam->begin;
                $param[] = $oneParam->end;
                $param[] = $oneParam->tag;
                $param[] = $oneParam->auther;
                $param[] = $oneParam->editor;
                $param[] = $oneParam->text;
                $param[] = $oneParam->language;
                $param[] = $oneParam->ver;
                $param[] = $oneParam->status;
                $param[] = mTime();
                $param[] = $oneParam->modify_time;

                $stmt->execute($param);
            }
            // 提交更改
            $PDO->commit();
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                $error = $PDO->errorInfo();
                echo "error - $error[2] <br>";
            } else {
                $count = count($arrData);
                echo "INSERT $count recorders." . "<br>";
            }
            break;
        }
    case "update":
        {
            echo "更在准备更新数据<br>";
            if (isset($_POST["data"])) {
                $data = $_POST["data"];
            } else {
                echo "没有输入数据<br>";
                return;
            }
            $arrData = json_decode($data);

            // 开始一个事务，关闭自动提交
            try {
                $PDO->beginTransaction();
                foreach ($arrData as $one) {
                    $query = "UPDATE sentence SET text='{$one->text}' ,
									receive_time='" . mTime() . "' ,
									modify_time='{$one->modify_time}'
								where id='{$one->guid}'";
                    $PDO->exec($query);
                }
                // 提交更改
                $PDO->commit();
                echo "update " . count($arrData) . "<br>";
            } catch (Exception $e) {
                $PDO->rollback();
                echo "Failed:" . $e->getMessage() . "<br>";
            }

            break;
        }
}
