<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../redis/function.php';


$redis = redis_connect();

$dns = _FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_PALITEXT_;
$dbh_pali_text = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_pali_text->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = _FILE_DB_RESRES_INDEX_;
$dbh_res = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_res->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if (isset($_GET["book"])) {
    $book = (int) $_GET["book"];
}
if (isset($_GET["para"])) {
    $para = (int) $_GET["para"];
}

$query = "SELECT chapter_len FROM "._TABLE_PALI_TEXT_." WHERE book = ? and paragraph = ?";
$stmt = $dbh_pali_text->prepare($query);
$stmt->execute(array($book, $para));
$paraInfo = $stmt->fetch(PDO::FETCH_ASSOC);
if ($paraInfo) {
    $query = "SELECT level FROM "._TABLE_PALI_TEXT_." WHERE book = ? and (paragraph between ? and ?) and level<8 order by level ASC limit 1";
    $stmt = $dbh_pali_text->prepare($query);
    $stmt->execute(array($book, $para + 1, $para + (int) $paraInfo["chapter_len"] - 1));
    $paraMax = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($paraMax) {
        $query = "SELECT book, paragraph as para, level , toc as title FROM "._TABLE_PALI_TEXT_." WHERE book = ? and (paragraph between ? and ?) and level = ? order by paragraph ASC limit 1000";
        $stmt = $dbh_pali_text->prepare($query);
        $stmt->execute(array($book, $para, $para + $paraInfo["chapter_len"], $paraMax["level"]));
        $paraList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($paraList as $key => $value) {
            # 查进度
            $paraProgress = false;
            if ($redis) {
                $count = $redis->hLen("progress_chapter_{$value["book"]}_{$value["para"]}");
                if ($count > 0) {
                    $prog = $redis->hGetAll("progress_chapter_{$value["book"]}_{$value["para"]}");
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
                $sth_toc->execute(array($value["book"], $value["para"]));
                $paraProgress = $sth_toc->fetch(PDO::FETCH_ASSOC);
            }

            $paraList[$key]["progress"] = $paraProgress;

            #查标题
            if (isset($_GET["lang"])) {
                $query = "SELECT title from \""._TABLE_RES_INDEX_."\" where book=? and paragraph=? and language=?";
                $stmt = $dbh_res->prepare($query);
                $sth_title = $dbh_res->prepare($query);
                $sth_title->execute(array($value["book"], $value["para"], $_GET["lang"]));
                $trans_title = $sth_title->fetch(PDO::FETCH_ASSOC);
                if ($trans_title) {
                    $paraList[$key]["trans_title"] = $trans_title['title'];
                }
            }
        }
        echo json_encode($paraList, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(array(), JSON_UNESCAPED_UNICODE);
    }

} else {
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}
