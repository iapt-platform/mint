<?php
/*
计算单词权重
 */
require_once '../path.php';
require_once './word_index_weight_table.php';

if (isset($_GET["from"])) {
    $from = (int)$_GET["from"];
    $to = (int)$_GET["to"];
} else {
    if ($argc != 3) {
        echo "无效的参数 ";
        exit;
    }
    $from = (int) $argv[1];
    $to = (int) $argv[2];
    if ($to > 217) {
        $to = 217;
    }
}

$dh_word = new PDO( _FILE_DB_WORD_INDEX_, "", "");
$dh_word->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dh_pali = new PDO( _FILE_DB_PALI_INDEX_, "", "");
$dh_pali->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

echo "from=$from to = $to \n";
for ($i = $from; $i <= $to; $i++) {
    $time_start = microtime(true);
    echo "正在处理 book= $i ";
    $query = "SELECT max(paragraph) from word where book=?";
	$stmt = $dh_pali->prepare($query);
    $stmt->execute(array($i));
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        $max_para = $row[0];
        echo "段落数量：$max_para \n";
        for ($j = 0; $j <= $max_para; $j++) {
            # code...
            $query = "SELECT id,book,wordindex,bold from word where book={$i} and paragraph={$j} order by id ASC";
            $stmt = $dh_pali->query($query);
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $query = "SELECT wordindex,count(*) as co from word where book={$i} and paragraph={$j} group by wordindex";
            $stmt = $dh_pali->query($query);
            $fetch_voc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $vocabulary = array();
            foreach ($fetch_voc as $key => $value) {
                $vocabulary[$value["wordindex"]] = $value["co"];
            }
            for ($iWord = 0; $iWord < count($fetch); $iWord++) {
                # 非黑体字
                if ($fetch[$iWord]["bold"] == 0) {
                    $count = $vocabulary[$fetch[$iWord]["wordindex"]];
                    $paraWeight = pow(1.01, $count); //总分
                    if ($paraWeight > 1.9) {
                        $paraWeight = 1.9;
                    }
                    $weight = $paraWeight / $count;
                } else {
                    #黑体字
                    #查找前后相连的黑体字
                    $begin = $iWord;
                    while ($fetch[$begin]["bold"] == 1) {
                        $begin--;
                        if ($begin < 0) {
                            break;
                        }
                    }
                    $begin = $begin + 1;

                    $end = $iWord;
                    while ($fetch[$end]["bold"] == 1) {
                        $end++;
                        if ($end > count($fetch) - 1) {
                            break;
                        }
                    }
                    $end = $end - 1;
                    $bold_count = $end - $begin + 1;
                    if ($bold_count == 1) {

                        $query = "SELECT * from wordindex where id=" . $fetch[$iWord]["wordindex"];
                        $stmt_word = $dh_word->query($query);
                        $wordinfo = $stmt_word->fetch(PDO::FETCH_ASSOC);
                        $bookId = (int) $fetch[$iWord]["book"];
                        if (mb_substr($wordinfo["word"], -2) == "ti") {
                            $weight = 100 + $book_weight[$bookId];
                        } else {
                            $weight = 100 + $book_weight[$bookId];
                        }
                        //echo "单独黑体 $weight \n";
                    } else {
                        #连续黑体字
                        //echo "连续黑体字";
                        $len_sum = 0;
                        $len_curr = 0;
                        for ($iBold = $begin; $iBold <= $end; $iBold++) {
                            # code...
                            $boldid = $fetch[$iBold]["wordindex"];
                            $query = "SELECT len from wordindex where id=" . $boldid;
                            $stmt_bold = $dh_word->query($query);
                            $wordbold = $stmt_bold->fetch(PDO::FETCH_ASSOC);
                            $len_sum += $wordbold["len"];
                            if ($iBold == $i) {
                                $len_curr = $wordbold["len"];
                            }
                        }
                        $weight = 10 + $len_curr / $len_sum;
                    }
                }
                //echo $weight."\n";
                $fetch[$iWord]["weight"] = (int) ($weight * 100);
            }
            # 将整段权重写入据库
            $dh_pali->beginTransaction();
            $query = "UPDATE word set weight = ? where id=? ";
            $stmt_weight = $dh_pali->prepare($query);
            foreach ($fetch as $key => $value) {
                $stmt_weight->execute(array($value["weight"], $value["id"]));
            }
            $dh_pali->commit();
            if (!$stmt_weight || ($stmt_weight && $stmt_weight->errorCode() != 0)) {
                $error = $dh_pali->errorInfo();
                echo "error - $error[2]";
            } else {
                //echo "修改数据库成功 book={$i} paragraph={$j} \n";
            }
        }
    } else {
        echo "无法获取段落最大值";
    }
    echo "处理时间 ：" . (microtime(true) - $time_start);
}
