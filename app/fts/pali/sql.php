<?php
require_once __DIR__."/../config.php";
/*
 * 该脚本用于生成 SQL 语句, 将三藏语料 CSV 数据 (如：abh01a.att.csv)
 * 转换为 SQL 语句插入到 PostgreSQL 内，数据表结构参见 fts.sql
 * 由于懒惰，没有优化脚本，占用了较多内存，所以执行时请多给 PHP 一些内存：
 * php -d memory_limit=1024M sql.php
 *
 */

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});

function is_pali_word ($str) {
  $pali_word_exp = "/^[āīūṅñṭḍṇḷṃṁŋĀĪŪṄÑṬḌṆḶṂṀŊabcdefghijklmnoprstuvyABCDEFGHIJKLMNOPRSTUVY-]+$/";

  return preg_match($pali_word_exp, $str) === 1;
}

/*
 *
 * 通过黑体字数组来计算黑体字连续出现的次数
 * 参数样例： ['a', '', '', 'b', 'c', 'd', '','','e','f', '', 'g','h']
 * 函数返回值样例：
 *
 * Array
 * (
 *     [bold_single] => a
 *     [bold_double] => e f , g h
 *     [bold_multiple] => b c d
 * )
 *
 *  */
function count_bld ($bld_array) {
  $prev = '';
  $bag = [];
  $result = [];

  // 添加最后一个空白结束占位符
  array_push($bld_array, '');

  foreach($bld_array as $v) {
    if (empty($v)) {
      $prev = $v;
      if (!empty($bag)) {
        array_push($result, $bag);
        $bag = [];
      }
      continue;
    } else {
      array_push($bag, $v);
    }
  }

  $final_result = [];
  foreach($result as $v) {
    $cnt = count($v);
    $content = join(' ', $v);
    if ($cnt == 1) {
      $key = 'bold_single';
    } else if ($cnt == 2) {
      $key = 'bold_double';
    } else if ($cnt > 2) {
      $key = 'bold_multiple';
    }

    if (empty($final_result[$key])) {
      $final_result[$key] = $content;
    } else {
      $final_result[$key] .= (' , ' . $content);
    }

  }

  return $final_result;
}


$dns = _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";";
$dbh_fts = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_fts->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 查找 tmp/palicsv/ 目录下的语料数据
$palicsv_path = __DIR__.'/../../tmp/palicsv';
$scan = scandir($palicsv_path);
$fileCounter = 0;
foreach($scan as $foldername) {
  if (is_dir("$palicsv_path/$foldername")) {

    $csv_file = "$palicsv_path/$foldername/$foldername.csv";

    // DEBUG
    // if ($foldername != 'abh01m.mul') continue;

    if (is_file($csv_file)) {
      $fileCounter++;      
      fwrite(STDOUT,"runing : $fileCounter" . PHP_EOL . $csv_file . PHP_EOL);
      // 初始化段落为 0 (没有这种段落)
      $paragraph = 0;
      // 初始化当前段落的黑体字数组
      $bold_text = [];
      
      if (($handle = fopen($csv_file, "r")) !== FALSE) {
        # 获取book id
        $data = fgetcsv($handle, 1000, ",");
        $data = fgetcsv($handle, 1000, ",");
        $bookId = (int)mb_substr($data[2],1);
        #删除旧数据
        $query = "DELETE FROM "._TABLE_FTS_." WHERE book=?";
        $stmt = $dbh_fts->prepare($query);
        $stmt->execute(array($bookId));

        // 开始一个事务，关闭自动提交
        $dbh_fts->beginTransaction();
        $query = "INSERT INTO "._TABLE_FTS_." (book , paragraph , wid,bold_single,bold_double,bold_multiple,content) VALUES ( ? , ? , ? , ? , ? , ? , ?  )";
        $stmt = $dbh_fts->prepare($query);

        rewind($handle);
        $row=0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          #忽略第一行
          if($row > 0){
            $current_word = $data[5];
            $style = $data[15];
            if ($style == 'paranum') {
              // 如果是段落编号，则保留数字
              $current_word = $data[4];
            } else if (!is_pali_word($current_word)) {
              /*
              * 如果当前单词不是巴利语单词，则忽略，当作它不存在
              * TODO 这样的处理方式，可能不合适，如下面场景：
              *          bld1 - bld2
              * bld1 和 bld2 是否应该分开对待呢？
              */
              continue;
            }

            if ($paragraph == $data[3]) {
              // 如果是同一段落，那么合并段落中的内容，中间加入空格
              $content .= ' ' . $current_word;

              // wid 取最后一个不为空的值 TODO （不一定合适）
              $wid =  empty($data[1]) ? $wid : $data[1];

              array_push($bold_text, $style == 'bld' ? $current_word : '');
            } else {
              // 如果是不同段落
              if ($paragraph !== 0) {
                // 如果刚才已经记录有数据，则转换为 SQL
                $bold_result = count_bld($bold_text);
                if(isset($bold_result['bold_single'])){
                  $bold_single = $bold_result['bold_single'];
                }else{
                  $bold_single = "";
                }
                if(isset($bold_result['bold_double'])){
                  $bold_double = $bold_result['bold_double'];
                }else{
                  $bold_double = "";
                }
                if(isset($bold_result['bold_multiple'])){
                  $bold_multiple = $bold_result['bold_multiple'];
                }else{
                  $bold_multiple = "";
                }
                
                $stmt->execute(array($book, $paragraph, $wid,$bold_single,$bold_double,$bold_multiple,$content));
                  // 转换后，重置黑体字数据
                $bold_text = [];
              }
              // 如果是不同段落，则赋新的值
              $content = $current_word;
              $paragraph =  (int)$data[3];
              $book = (int)mb_substr($data[2],1);
              $wid =  $data[1];

              array_push($bold_text, $style == 'bld' ? $current_word : '');
            }
          }
          $row++;

        }
        fclose($handle);
        // 提交更改
        $dbh_fts->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = $dbh_fts->errorInfo();
            fwrite(STDERR, "error - $error[2]".PHP_EOL);
        } else {
            fwrite(STDOUT, "updata $row recorders.".PHP_EOL);
        }	
      }


      // DEBUG 仅生成一个文件，测试用
      // exit;
    }
  }
}

fwrite(STDOUT, "Done. Amitābha".PHP_EOL);
?>
