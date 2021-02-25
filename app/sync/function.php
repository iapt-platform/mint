<?php
require_once "../public/function.php";

function do_sync($param)
{

    if (isset($_GET["op"])) {
        $op = $_GET["op"];
    } else if (isset($_POST["op"])) {
        $op = $_POST["op"];
    } else {
        echo "error: no op";
        return (false);
    }

    $PDO = new PDO("" . $param->database, "", "", array(PDO::ATTR_PERSISTENT => true));
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    switch ($op) {
        case "sync":
            {
                $time = $_POST["time"];
                $query = "select {$param->uuid} as guid, {$param->modify_time} from {$param->table}  where {$param->receive_time} > '{$time}' limit 0,10000";
                $stmt = $PDO->query($query);
                $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $iFetch = count($Fetch);
                echo (json_encode($Fetch, JSON_UNESCAPED_UNICODE));
                break;
            }
        case "get":
            {
                if (isset($_GET["id"])) {
                    $id = $_GET["id"];
                } else if (isset($_POST["id"])) {
                    $id = $_POST["id"];
                } else {
                    return (false);
                }
                $arrId = json_decode($id);
                $queryId = "('";
                foreach ($arrId as $one) {
                    $queryId .= $one . "','";
                }
                $queryId = substr($queryId, 0, -2) . ")";
                $query = "SELECT * FROM {$param->table} WHERE {$param->uuid} in {$queryId}";

                $stmt = $PDO->query($query);
                $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo (json_encode($Fetch, JSON_UNESCAPED_UNICODE));
                return (true);
                break;
            }
        case "insert":
            {
                echo "正在准备插入记录<br>";
                if (isset($_POST["data"])) {
                    $data = $_POST["data"];
                } else {
                    echo "没有数据<br>";
                    return (false);
                }

                // 开始一个事务，关闭自动提交

                $PDO->beginTransaction();
                $query = "INSERT INTO {$param->table} (";
                foreach ($param->insert as $row) {
                    $query .= "'" . $row . "',";
                }
                $query .= "'receive_time') VALUES ( ";
                for ($i = 0; $i < count($param->insert); $i++) {
                    $query .= " ?, ";
                }
                $query .= " ? )";

                $arrData = json_decode($data, true);
                $stmt = $PDO->prepare($query);
                foreach ($arrData as $oneParam) {
                    $newRow = array();
                    foreach ($param->insert as $row) {
                        $newRow[] = $oneParam["{$row}"];
                    }
                    $newRow[] = mTime();

                    $stmt->execute($newRow);
                }
                // 提交更改
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = $PDO->errorInfo();
                    echo "error - $error[2] <br>";
                    return (false);
                } else {
                    $count = count($arrData);
                    echo "INSERT $count recorders." . "<br>";
                    return (true);
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
                    return (false);
                }
                $arrData = json_decode($data, true);
                $query = "UPDATE {$param->table} SET ";
                foreach ($param->update as $row) {
                    $query .= "{$row} = ? ,";
                }
                $query .= "{$param->receive_time} = ?  where {$param->uuid} = ? ";
                $stmt = $PDO->prepare($query);
                // 开始一个事务，关闭自动提交
                try {
                    $PDO->beginTransaction();
                    foreach ($arrData as $one) {

                        $newRow = array();
                        foreach ($param->update as $row) {
                            $newRow[] = $one["{$row}"];
                        }
                        $newRow[] = mTime();
                        $newRow[] = $one["{$param->uuid}"];
                        $stmt->execute($newRow);
                    }
                    // 提交更改
                    $PDO->commit();
                    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                        $error = $PDO->errorInfo();
                        echo "error - $error[2] <br>";
                        return (false);
                    } else {
                        $count = count($arrData);
                        echo "INSERT $count recorders." . "<br>";
                        return (true);
                    }
                } catch (Exception $e) {
                    $PDO->rollback();
                    echo "Failed:" . $e->getMessage() . "<br>";
                    return (false);
                }

                break;
            }
        default:
            break;
    }

}
