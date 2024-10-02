<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../install/filelist.php";
require_once __DIR__."/../redis/function.php";

if (PHP_SAPI == "cli") {
    if ($argc >= 2) {
        $command = $argv[1];
    } else {
        $redis = redis_connect();
        if ($redis == false) {
            echo "no redis connect\n";
            exit;
		}
		
        $dirXmlBase = _DIR_PALI_CSV_ . "/";

        $book = array(1, 2, 3, 4, 5, 6, 7, 8, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 153, 152, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217);
        $redis->delete('pali://wordindex.set');
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
                            $redis->sadd('pali://wordindex.set', $data[5]);
                        }
                    }
                }
				fclose($fp);
            } else {
                echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
			}

		}
		echo "set done".$redis->scard("pali://wordindex.set")."\n";

		$redis->delete('pali://wordindex.hash');
		$i = null;
		$counter = 0;
		while ($words = $redis->sscan("pali://wordindex.set", $i)) {
			foreach ($words as $key => $value) {
				# code...
				$counter++;
				$redis->hSet("pali://wordindex.hash",$counter,$value);
			}
		}
		echo "hash done".$redis->hLen("pali://wordindex.hash")."\n";
    }
} else {
    echo "cli";
}

echo "<h2>齐活！功德无量！all done!</h2>";
