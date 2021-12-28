<?php

require_once '../config.php';

set_error_handler(function(int $number, string $message) {
	echo "Handler captured error $number: '$message'" . PHP_EOL  ;
 });
 
$filename = $_GET["filename"];
$dbname = $_GET["dbname"];
if(isset($_GET["table"])){
	$table = $_GET["table"];
}
switch ($_GET["dbtype"]) {
    case "rich":
    case "system":
        if ($_GET["dbtype"] == "rich") {
            $sDescDbFile = _DIR_DICT_3RD_ . "/" . $dbname;
            $csvfile = _DIR_DICT_TEXT_ . "/rich/{$filename}";
        } else if ($_GET["dbtype"] == "system") {
            $sDescDbFile = _DIR_DICT_SYSTEM_ . "/" . $dbname;
            $csvfile = _DIR_DICT_TEXT_ . "/system/{$filename}";
        }
        $dns = "sqlite:" . $sDescDbFile;
        $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        if (strstr($filename, ".", true) === strstr($dbname, ".", true)) {
            //建立数据库
            $_sql = file_get_contents('rich_dict.sql');
            $_arr = explode(';', $_sql);
            //执行sql语句
            foreach ($_arr as $_value) {
                $dbh->query($_value . ';');
            }
            echo $dns . "建立数据库成功<br>";
		}
		
		//$query = "DELETE from dict where 1";
		//$dbh->query($query);
        // 开始一个事务，关闭自动提交
        $dbh->beginTransaction();

        $query = "INSERT INTO dict ('id','pali', 'type', 'gramma', 'parent', 'mean', 'note', 'parts', 'partmean', 'status', 'confidence', 'len', 'dict_name', 'lang') VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $dbh->prepare($query);
        $count = 0;
        // 打开文件并读取数据
        if (($fp = fopen($csvfile, "r")) !== false) {
			echo "正在处理 {$csvfile} <br>";
            while (($data = fgetcsv($fp, 0, ',')) !== false) {
				//id,wid,book,paragraph,word,real,type,gramma,mean,note,part,partmean,bmc,bmt,un,style,vri,sya,si,ka,pi,pa,kam
				try {
					//code...
					$stmt->execute($data);
				} catch (Throwable $e) {
					//throw $th;
					echo "幺蛾子数据在第{$count}行<br>";
					echo "Captured Throwable: " . $e->getMessage() . PHP_EOL;
				}
                
                $count++;
            }
            fclose($fp);
            echo "单词表load<br>";
        } else {
            echo "can not open csv file. ";
        }

        // 提交更改
        $dbh->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = $dbh->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            echo "updata $count recorders.";
        }

        $dbh = null;

        break;
    case "thin":
        echo "doing filename: $filename dbname: $dbname <br>";
        $sDescDbFile = _DIR_DICT_SYSTEM_ . "/" . $dbname;
        $csvfile = _DIR_DICT_TEXT_ . "/thin/{$filename}";
        $dns = "sqlite:" . $sDescDbFile;
        $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        // 开始一个事务，关闭自动提交
        $dbh->beginTransaction();
        if ($dbname === "ref.db") {
            if ($table === "dict") {
                $query = "INSERT INTO dict ('id','language', 'dict_id', 'eword', 'word', 'paliword', 'mean', 'length', 'status') VALUES ( ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ? ,  ?  )";
            } else if ($table === "info") {
                $query = "INSERT INTO info ('language' , 'id' ,  'shortname' , 'name') VALUES (  ? ,  ? ,  ? ,  ? )";
            } else {
                echo "table name  unkow.";
            }
        } else if ($dbname === "ref1.db") {
            $query = "INSERT INTO dict ('id','eword', 'word', 'length', 'count') VALUES (  ? ,  ? ,  ? ,  ? ,  ? )";
        } else {
            echo "db name $dbname unkow.";
        }

        $stmt = $dbh->prepare($query);
        $count = 0;
        // 打开文件并读取数据
        if (($fp = fopen($csvfile, "r")) !== false) {
            while (($data = fgetcsv($fp, 0, ',')) !== false) {
                $stmt->execute($data);
                $count++;
            }
            fclose($fp);
            echo "单词表load<br>";
        } else {
            echo "can not open csv file. ";
        }

        // 提交更改
        $dbh->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = $dbh->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            echo "updata $count recorders.";
        }

        $dbh = null;

        break;
    case "part":
        $dns = "" . _FILE_DB_PART_;
        $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		//建立数据库
		$_sql = file_get_contents('part.sql');
		$_arr = explode(';', $_sql);
		//执行sql语句
		foreach ($_arr as $_value) {
			$dbh->query($_value . ';');
		}
		echo $dns . "建立数据库成功<br>";

        // 开始一个事务，关闭自动提交
        $dbh->beginTransaction();

        $query = "INSERT INTO part ('word','weight') VALUES ( ?, ? )";
        $stmt = $dbh->prepare($query);
        $count = 0;
        // 打开文件并读取数据
        if (($fp = fopen(_DIR_DICT_TEXT_ . "/system/part.csv", "r")) !== false) {
            while (($data = fgetcsv($fp, 0, ',')) !== false) {
                $stmt->execute($data);
                $count++;
            }
            fclose($fp);
            echo "part load ";
        } else {
            echo "can not open csv file. ";
        }

        // 提交更改
        $dbh->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = $dbh->errorInfo();
            echo "error - $error[2] <br>";
        } else {
            echo "updata $count recorders.";
        }

        $dbh = null;

        break;
}
