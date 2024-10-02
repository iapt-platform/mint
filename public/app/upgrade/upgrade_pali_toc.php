<?php
#升级段落完成度数据库
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../redis/function.php';

$redis = redis_connect();

$dns = _FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_SENTENCE_;
$dbh_sent = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_PALI_SENTENCE_;
$dbh_pali_sent = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_pali_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_PALITEXT_;
$dbh_pali_text = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_pali_text->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$valid_book = array();

#第一步 查询有多少书有译文
$query = "SELECT book_id as book from "._TABLE_SENTENCE_." where strlen>0 and word_start is not null  and language<>'' and book_id <1000  group by book_id";
$stmt = $dbh_sent->prepare($query);
$stmt->execute();
$valid_book = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "book:" . count($valid_book) . "<br>\n";

#第一步 查询语言
$query = "SELECT language from "._TABLE_SENTENCE_." where strlen>0 and word_start is not null  and language<>'' and book_id <1000   group by language";
$stmt = $dbh_sent->prepare($query);
$stmt->execute();
$result_lang = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "lang:" . count($result_lang) . "<br>\n";

$query = "DELETE FROM "._TABLE_PROGRESS_." WHERE true";
$sth_toc = $dbh_toc->prepare($query);
$sth_toc->execute();
$query = "DELETE FROM "._TABLE_PROGRESS_CHAPTER_." WHERE true";
$sth_toc = $dbh_toc->prepare($query);
$sth_toc->execute();

/* 开始一个事务，关闭自动提交 */
$dbh_toc->beginTransaction();
$query = "INSERT INTO "._TABLE_PROGRESS_." (book, para , lang , all_strlen , public_strlen) VALUES (?, ?, ? , ? ,? )";
$sth_toc = $dbh_toc->prepare($query);
foreach ($result_lang as $lang) {
    # 第二步 生成para progress 1,2,15,zh-tw

    #查询该语言有多少段
    $query = "SELECT book_id as book,paragraph from "._TABLE_SENTENCE_." where strlen>0 and language= ? and book<1000 group by book,paragraph";
    $stmt = $dbh_sent->prepare($query);
    $stmt->execute(array($lang["language"]));
    $result_para = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result_para as $para) {

        # 查询每个段落的等效巴利语字符数
        $query = "SELECT word_start from "._TABLE_SENTENCE_." where strlen>0 and language= ? and book_id = ? and paragraph = ? and word_start is not null group by word_start,word_end";
        $stmt = $dbh_sent->prepare($query);
        $stmt->execute(array($lang["language"], $para["book"], $para["paragraph"]));
        $result_sent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result_sent) > 0) {
            echo "book:{$para["book"]} para: {$para["paragraph"]}\n";
            #查询这些句子的总共等效巴利语字符数
            $place_holders = implode(',', array_fill(0, count($result_sent), '?'));
            $query = "SELECT sum(length) as strlen from "._TABLE_PALI_SENT_." where book = ? and paragraph = ? and word_begin in ($place_holders)";
            $sth = $dbh_pali_sent->prepare($query);
            $param = array();
            $param[] = $para["book"];
            $param[] = $para["paragraph"];
            foreach ($result_sent as $sent) {
                # code...
                $param[] = (int) $sent["word_start"];
            }
            $sth->execute($param);
            $result_strlen = $sth->fetch(PDO::FETCH_ASSOC);
            if ($result_strlen) {
                $para_strlen = $result_strlen["strlen"];
            } else {
                $para_strlen = 0;
            }
			if($redis){
				$redis->hSet("progress_{$para["book"]}-{$para["paragraph"]}", $lang["language"], $para_strlen);
			}

			$sth_toc->execute(array($para["book"], $para["paragraph"], $lang["language"], $para_strlen, 0));

        }
    }
}
$dbh_toc->commit();
if (!$sth_toc || ($sth_toc && $sth_toc->errorCode() != 0)) {
    /*  识别错误且回滚更改  */
    $sth_toc->rollBack();
    $error = $dbh_toc->errorInfo();
    echo "error:" . $error[2] . "\n";
}

#第三步生成 章节完成度库
/* 开始一个事务，关闭自动提交 */
$dbh_toc->beginTransaction();
$query = "INSERT INTO "._TABLE_PROGRESS_CHAPTER_." (book, para , lang , all_trans,public) VALUES (?, ?, ? , ? ,? )";
$sth_toc = $dbh_toc->prepare($query);

foreach ($valid_book as $key => $book) {
    echo "doing chapter in book " . $book["book"] . "\n";
    # code...
    $query = "SELECT paragraph , chapter_len from "._TABLE_PALI_TEXT_." where level < 8 and book = ?";
    $stmt = $dbh_pali_text->prepare($query);
    $stmt->execute(array($book["book"]));
    $result_chapter = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result_chapter as $key => $chapter) {
        # 查询巴利字符数
        $query = "SELECT sum(strlen) as pali_strlen from "._TABLE_PALI_SENT_INDEX_." where book = ? and para between ? and ? ";
        $stmt = $dbh_pali_sent->prepare($query);
        $stmt->execute(array($book["book"], $chapter["paragraph"], (int) $chapter["paragraph"] + (int) $chapter["chapter_len"] - 1));
        $result_chapter_strlen = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result_chapter_strlen) {
            $pali_strlen = (int) $result_chapter_strlen["pali_strlen"];
            # 译文等效字符数
            foreach ($result_lang as $lang) {

                if ($redis) {
                    $tran_strlen = 0;
                    for ($i = $chapter["paragraph"]; $i < (int) $chapter["paragraph"] + (int) $chapter["chapter_len"]; $i++) {
                        # code...
                        $all_strlen = $redis->hGet("progress_{$book["book"]}-{$i}", $lang["language"]);
                        if ($all_strlen) {
                            $tran_strlen += $all_strlen;
                        }
                    }
                    if ($tran_strlen > 0) {
                        $progress = $tran_strlen / $pali_strlen;
                        $redis->hSet("progress_chapter_{$book["book"]}_{$chapter["paragraph"]}", $lang["language"], $progress);
                    }
                } 
				
                $query = "SELECT sum(all_strlen) as all_strlen from "._TABLE_PROGRESS_." where book = ? and (para between ? and ? )and lang = ?";
                $stmt = $dbh_toc->prepare($query);
                $stmt->execute(array($book["book"], $chapter["paragraph"], (int) $chapter["paragraph"] + (int) $chapter["chapter_len"] - 1, $lang["language"]));
                $result_chapter_trans_strlen = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result_chapter_trans_strlen) {
                    $tran_strlen = (int) $result_chapter_trans_strlen["all_strlen"];
                    if ($tran_strlen > 0) {
                        $progress = $tran_strlen / $pali_strlen;
                        $sth_toc->execute(array($book["book"], $chapter["paragraph"], $lang["language"], $progress, 0));
                    }
                }
                
                #插入段落数据
            }
        }
    }
}
$dbh_toc->commit();
if (!$sth_toc || ($sth_toc && $sth_toc->errorCode() != 0)) {
    /*  识别错误且回滚更改  */
    $sth_toc->rollBack();
    $error = $dbh_toc->errorInfo();
    echo "error:" . $error[2] . "\n";
}
