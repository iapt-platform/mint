<?php
header('Content-type: application/json; charset=utf8');

//查询term字典
require_once "../config.php";

$PDO = new PDO("" . _FILE_DB_TERM_, "", "", array(PDO::ATTR_PERSISTENT => true));
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
            $query = "select guid,modify_time from term  where receive_time>'{$time}' limit 0,1000";

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
            $query = "select guid, word, word_en, meaning, other_meaning, note, tag, create_time, owner, hit,language,receive_time,modify_time from term  where guid in {$queryId}";

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
            $query = "INSERT INTO term ('id',
								'guid',
								'word',
								'word_en',
								'meaning',
								'other_meaning',
								'note',
								'tag',
								'create_time',
								'owner',
								'hit',
								'language',
								'receive_time',
								'modify_time'
								) VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $PDO->prepare($query);
            foreach ($arrData as $oneParam) {
                $param = array();
                $param[] = $oneParam->guid;
                $param[] = $oneParam->word;
                $param[] = $oneParam->word_en;
                $param[] = $oneParam->meaning;
                $param[] = $oneParam->other_meaning;
                $param[] = $oneParam->note;
                $param[] = $oneParam->tag;
                $param[] = $oneParam->create_time;
                $param[] = $oneParam->owner;
                $param[] = $oneParam->hit;
                $param[] = $oneParam->language;
                $param[] = time();
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
                    $query = "UPDATE term SET word='{$one->word}' ,
									word_en='{$one->word_en}' ,
									meaning='{$one->meaning}' ,
									other_meaning='{$one->other_meaning}' ,
									note='{$one->note}' ,
									tag='{$one->tag}' ,
									create_time='{$one->create_time}' ,
									owner='{$one->owner}' ,
									hit='{$one->hit}' ,
									language='{$one->language}' ,
									receive_time='" . time() . "' ,
									modify_time='{$one->modify_time}'
								where guid='{$one->guid}'";
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
