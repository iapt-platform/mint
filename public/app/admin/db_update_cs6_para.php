<?php
#将cscd4 段落编号插入数据库 用于义注跳转
require_once "../public/_pdo.php";
require_once "../config.php";

PDO_Connect(_FILE_DB_PALITEXT_);
$query = "SELECT * from "._TABLE_PALI_BOOK_NAME_;
$books = PDO_FetchAll($query);

PDO_Connect(_FILE_DB_PAGE_INDEX_);
// 打开文件并读取数据
$irow = 0;
if (($fp = fopen("./cs6_para.csv", "r")) !== false) {
    // 开始一个事务，关闭自动提交
    $PDO->beginTransaction();
    $query = "INSERT INTO cs6_para (book,para,bookid,cspara,book_name) VALUES (  ? , ? , ? , ? , ? )";
    $stmt = $PDO->prepare($query);

    // 提交更改
    try {
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            $irow++;
            if ($irow > 1) {
                $book_id = 0;
                foreach ($books as $key => $value) {
                    # code...
                    if ($value["book"] == $data[0] && $data[1] >= $value["paragraph"]) {
                        $book_id = $value["id"];
                        break;
                    }
                }
                if ($data[3] == $data[4]) {
                    $stmt->execute(array($data[0], $data[1], $book_id, $data[3], $data[2]));
                } else {
                    $begin = (int) $data[3];
                    $end = (int) $data[4];
                    $arr1 = array();
                    for ($i = $begin; $i <= $end; $i++) {
                        $arr1[] = $i;
                    }
                    foreach ($arr1 as $key => $value) {
                        $stmt->execute(array($data[0], $data[1], $book_id, $value, $data[2]));
                    }
                }

            }
        }
        $PDO->commit();
    } catch (Exception $e) {
        var_dump($e);
        $PDO->rollback();
    }

    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        echo "error - $error[2] \n";
    } else {
        echo "updata  recorders.\n";
    }

    fclose($fp);
} else {
    echo "can not open csv file. cs6_para.csv";
}

echo "齐活！功德无量！all done!";
