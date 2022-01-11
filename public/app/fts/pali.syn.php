<?php

/*
 * 该脚本用于生成 pali.syn, 调用时请给 PHP 分配足够的内存，如：
 * php -d memory_limit=1024M pali.syn.php
 *
 */

// 存放 pali.syn 文件内的所有数据，
// 存到内存里是要最后排序用
$pali_syn_array = array();

// 查找 dicttext/system 目录下的所有词形转换文件
foreach (glob("../../dicttext/system/sys_*regular*.csv") as $filename) {
  echo "Handling file: $filename \n";
  $row = 1;
  if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      // echo "\n==== line $row ====\n";
      $row++;

      // 如果单词的原形存在，并且单词本身和原形不相同
      if (!empty($data[4]) && strcmp($data[1], $data[4]) !== 0) {
        // echo 'COLLECTING:' . $data[1] . ' -> ' . $data[4] . "\n";

        // 使用 key => value 方式
        // 将会移除一个单词对应多个原型的场景，选取最长的结果作为原形
        // 如:
        // abahumānaṃ -> abahumāna (sys_regular1.csv)
        // abahumānaṃ -> abahumata (sys_regular2.csv)
        // 这个方法合适不合适，还有待商议，暂且这样
        if (
          // 暂无数据
          empty($pali_syn_array[$data[1]])
          || // 或者
          // 数据较短
          strcmp($pali_syn_array[$data[1]], $data[4]) < 0
        ) {
          $pali_syn_array[$data[1]] = $data[4];
        }
      } else {
        // echo 'SKIPPING:' . $data[1] . ' -> ' . $data[4] . "\n";
      }
    }
    fclose($handle);
  }
}

echo "Sorting contents ... \n";

// sort($pali_syn_array);
// $pali_syn_array = array_unique($pali_syn_array);

echo "Writing into pali.syn ... \n";

// 写入当前目录：pali.syn
$filename = 'pali.syn';
// c 模式，直接覆盖当前文件
if (!$handle = fopen($filename, 'c')) {
  echo "Cannot open file ($filename)";
  exit;
}
// 写入 UTF8 文件头
$content = "\xEF\xBB\xBF";
if (fwrite($handle, $content) === FALSE) {
  echo "Cannot write to file ($filename) with ($content)";
  exit;
}

// 逐行写入内容
foreach ($pali_syn_array as $k => $v) {
  $content = $k . ' ' . $v . PHP_EOL;
  // echo $content;
  if (fwrite($handle, $content) === FALSE) {
    echo "Cannot write to file ($filename) with ($content)";
    exit;
  }
}

fclose($handle);

echo "Done. Amitābha \n";
?>
