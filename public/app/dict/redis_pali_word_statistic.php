<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../install/filelist.php";
require_once __DIR__."/../redis/function.php";
$redis = redis_connect();
$strKey='pali://wordstatisitic.hash';
if (PHP_SAPI == "cli") {
    if ($argc >= 2) {
        $command = $argv[1];
    } else {
		exit;
	}
	{

        if ($redis == false) {
            echo "no redis connect\n";
            exit;
		}

		switch ($command) {
			case 'init':
				# code...
			    $dirXmlBase = _DIR_PALI_CSV_ . "/";

			    $book = array(1, 2, 3, 4, 5, 6, 7, 8, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 153, 152, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217);
			    $redis->delete($strKey);
                foreach ($book as $key => $value) {
                    # code...
                    echo "runing:{$value}\n";
                    $outputFileNameHead = $_filelist[$value];
                    $dirXml = $outputFileNameHead . "/";
                    // 打开文件并读取数据
                    $irow = 0;
                    if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
                        while (($str = fgets($fp)) !== false) {
                            $data = explode(",", $str);
                            $irow++;
                            if ($irow > 1) {
                                if ($data[6] != ".ctl." && $data[5] != "") {
                                    $word = $redis->hGet($strKey, $data[5]);
                                    if($word){
                                        $eWord = json_decode($word);
                                        $eWord->count++;
                                    }else{
                                        $eWord = [];
                                        $eWord["word"]=$data[5];
                                        $eWord["count"]=1;
                                        $eWord["ref"]=0;
                                        $eWord["user"]=0;
                                        $eWord["len"]=mb_strlen($data[5],"UTF-8");
                                    }
                                    $redis->hSet($strKey, $data[5],json_encode($eWord,JSON_UNESCAPED_UNICODE));
                                }
                            }
                        }
                        fclose($fp);
                    } else {
                        echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
                    }

                }
                echo "hash done".$redis->hLen($strKey).PHP_EOL;
                break;
			case "ref":
				$dbh = new PDO(_DICT_DB_REGULAR_, "", "", array(PDO::ATTR_PERSISTENT => true,PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY));
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

				$query = "SELECT pali from "._TABLE_DICT_REGULAR_." where 1  group by pali";
				$stmt = $dbh->prepare($query);
				$stmt->execute();
				$count = 0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					# code...
					$word = $redis->hGet($strKey, $row["pali"]);
					if($word){
						$eWord = json_decode($word,true);
						$eWord["ref"]=1;
						$redis->hSet($strKey, $row["pali"],json_encode($eWord,JSON_UNESCAPED_UNICODE));
						$count++;
					}
				}
				echo "regular count:".$count.PHP_EOL;

				$dbh = new PDO(_DICT_DB_IRREGULAR_, "", "", array(PDO::ATTR_PERSISTENT => true,PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY));
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

				$query = "SELECT pali from "._TABLE_DICT_IRREGULAR_." where 1  group by pali";
				$stmt = $dbh->prepare($query);
				$stmt->execute();
				$count = 0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					# code...
					$word = $redis->hGet($strKey, $row["pali"]);
					if($word){
						$eWord = json_decode($word,true);
						$eWord["ref"]=1;
						$redis->hSet($strKey, $row["pali"],json_encode($eWord,JSON_UNESCAPED_UNICODE));
						$count++;
					}
				}
				echo "irregular count:".$count.PHP_EOL;


				break;
			case 'update':
				$dbh = new PDO(_FILE_DB_WBW_, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true,PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY));
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

				$query = "SELECT word from "._TABLE_DICT_WBW_."  group by word";
				$stmt = $dbh->prepare($query);
				$stmt->execute();
				$count = 0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					# code...
					$word = $redis->hGet($strKey, $row["word"]);
					if($word){
						$eWord = json_decode($word,true);
						$eWord["user"]=1;
						$redis->hSet($strKey, $row["word"],json_encode($eWord,JSON_UNESCAPED_UNICODE));
						$count++;
					}
				}
				echo "user wbw count:".$count.PHP_EOL;
				$start=1;
				$all=0;
				$ref=0;
				$user=0;
				$cover=0;

				$all1=0;
				$ref1=0;
				$user1=0;
				$cover1=0;
				while($word = $redis->hGet("pali://wordindex.hash",$start)){
					$eWord = $redis->hGet($strKey, $word);
					$eWord = json_decode($eWord,true);
					$all += $eWord["count"]*$eWord["len"];
					$all1++;
					$ref += $eWord["ref"]*$eWord["len"];
					$ref1 += $eWord["ref"];
					$user += $eWord["user"]*$eWord["len"];
					$user1 += $eWord["user"];
					if($eWord["ref"]==1 || $eWord["user"]==1){
						$cover += $eWord["len"];
						$cover1 += 1;
					}
					$start++;
				}
				$file = fopen(_DIR_LOG_."/dict_cover.log","a");
				if($file){
					fputs($file,date("Y-m-d h:i:sa").sprintf("字符数 全部:{$all},参考:{$ref},用户字典:{$user},总和:{$cover},单词数 全部:{$all1},参考:{$ref1},用户字典:{$user1},总和:{$cover1}",$all,$ref,$user,$cover,$all1,$ref1,$user1,$cover1));
					fclose($file);
				}
				echo "字符数 全部:{$all},参考:{$ref},用户字典:{$user},总和:{$cover}".PHP_EOL;
				echo "单词数 全部:{$all1},参考:{$ref1},用户字典:{$user1},总和:{$cover1}".PHP_EOL;
				break;

			default:
				# code...
				break;
		}
   }
} else {
		echo " null ".PHP_EOL;

}

echo "<h2>齐活！功德无量！all done!</h2>";
