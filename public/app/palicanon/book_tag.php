<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../redis/function.php';


$redis = redis_connect();

$tag = str_getcsv($_GET["tag"], ","); //
$arrBookTag = json_decode(file_get_contents("../public/book_tag/en.json"), true);
$countTag = count($tag);
$output = array();
foreach ($arrBookTag as $bookkey => $bookvalue) {
    $isfind = 0;
    foreach ($tag as $tagkey => $tagvalue) {
        if (strpos($bookvalue["tag"], ':' . $tagvalue . ':') !== false) {
            $isfind++;
        }
    }
    if ($isfind == $countTag) {
        $output[] = $bookvalue;
    }
}

$dns = _FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_PALITEXT_;
$dbh_pali_text = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_pali_text->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_RESRES_INDEX_;
$dbh_res = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_res->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

foreach ($output as $key => $value) {
    # code...
    $book = (int) $value["book"];
    $para = (int) $value["para"];
    $level = (int) $value["level"];
    if (count($output) < 100 || (count($output) > 100 && $level == 1)) {
        $query = "SELECT * FROM "._TABLE_PALI_TEXT_." WHERE book = ? and paragraph = ?";
        $stmt = $dbh_pali_text->prepare($query);
        $stmt->execute(array($book, $para));
        $paraInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($paraInfo) {
            # 查进度
            $paraProgress = false;
            if ($redis) {
                $count = $redis->hLen("progress_chapter_{$book}_{$para}");
                if ($count > 0) {
                    $prog = $redis->hGetAll("progress_chapter_{$book}_{$para}");
                    foreach ($prog as $keylang => $valuetrans) {
                        # code...
                        $paraProgress = array("lang" => $keylang, "all_trans" => $valuetrans);
                        break;
                    }
                }
            } else {
                $query = "SELECT lang, all_trans from "._TABLE_PROGRESS_CHAPTER_." where book=? and para=?";
                $stmt = $dbh_toc->prepare($query);
                $sth_toc = $dbh_toc->prepare($query);
                $sth_toc->execute(array($book, $para));
                $paraProgress = $sth_toc->fetch(PDO::FETCH_ASSOC);
            }

            $output[$key]["progress"] = $paraProgress;

            #查标题
            if (isset($_GET["lang"])) {
                $query = "SELECT title from \""._TABLE_RES_INDEX_."\" where book=? and paragraph=? and language=?";
                $stmt = $dbh_res->prepare($query);
                $sth_title = $dbh_res->prepare($query);
                $sth_title->execute(array($book, $para, $_GET["lang"]));
                $trans_title = $sth_title->fetch(PDO::FETCH_ASSOC);
                if ($trans_title) {
                    $output[$key]["trans_title"] = $trans_title['title'];
                }
            }
        }
    }
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
