<?php
include "./_pdo.php";
include "config.php";

$input = file_get_contents("php://input"); //

//begin updata wbw database
$log = "";

$arrInserString = array();
$xmlObj = simplexml_load_string($input);
$dataBlock = $xmlObj->xpath('//block');
foreach ($dataBlock as $block) {
    if ($block->info->type == "wbw") {
        $bookId = $block->info->book;
        $parNum = $block->info->paragraph;
        $author = $block->info->author;
        $language = $block->info->language;
        $edition = $block->info->edition;
        $db_file = $dir_palicannon_wbw . $bookId . "_wbw.db3";
        PDO_Connect("$db_file");
        $log .= "pdo open . $db_file \r\n";

        $query = "DELETE FROM \"main\" WHERE \"book\" = " . $PDO->quote($bookId) . " AND \"paragraph\" = " . $PDO->quote($parNum);
        $stmt = @PDO_Execute($query);
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            //print_r($error[2]);
            $log .= "error:" . $error[2] . " \r\n";
            break;
        }
        $log .= "pdo delete ok . $db_file \r\n";
        $arrInserString = array();
        $words = $block->data->xpath('word');
        foreach ($block->data->children() as $ws) {
            $log .= "word:$ws->pali \r\n";
            //foreach($words as $ws){
            //new recorder
            $params = array($ws->id, $ws->pali, $ws->real, $ws->type, $ws->gramma, $ws->mean, $ws->note, $ws->org, $ws->om, $ws->bmc, $ws->bmt, $ws->un, $ws->style, $ws->edition);
            $arrInserString[count($arrInserString)] = $params;
        }

        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query = "INSERT INTO main ('id','wid','book','paragraph','word','real','type','gramma','mean','note','part','partmean','bmc','bmt','un','style','language','author','editor','revision','edition','subver','vri','sya','si','ka','pi','pa','kam') VALUES (null,?,'" . $bookId . "','$parNum',?,?,?,?,?,?,?,?,?,?,?,?,'" . $language . "','" . $author . "','','',?,'0','1','0','0','0','0','0','0')";
        $log .= "query $query \r\n";
        $stmt = $PDO->prepare($query);
        foreach ($arrInserString as $oneParam) {
            $stmt->execute($oneParam);
        }
        /* 提交更改 */
        $PDO->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            $log .= "error - $error[2] \r\n";
        } else {
            $count = count($arrInserString);
            $log .= "updata $count recorders. \r\n";
        }

        $PDO = null;
        $log .= "pdo close. \r\n";
    }
}
$myLogFile = fopen("updata_wbw.log", "w");
fwrite($myLogFile, $log);
fclose($myLogFile);
echo ("Successful");
