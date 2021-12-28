<?php
$currBook = $_GET["book"];
$currParagraph = $_GET["paragraph"];

$res_type = $_GET["res_type"];
$language = $_GET["language"];
$author = $_GET["author"];
$editor = $_GET["editor"];
$revision = $_GET["revision"];
$edition = $_GET["edition"];
$subver = $_GET["subver"];

include "./config.php";
include "./_pdo.php";
include "./public.inc";

$countInsert = 0;
$wordlist = array();
$tempFile = "temp.txt";
$guid = GUIDv4();

switch ($res_type) {
    case "wbw":
        if ($author == "templet") {
            $db_file = "../appdata/palicanon/templet/" . $currBook . "_tpl.db3";
        } else {
            $db_file = "../appdata/palicanon/wbw/" . $currBook . "_wbw.db3";
        }
        break;
    case "heading":
        if ($author == "templet") {
            $db_file = _FILE_DB_PALITEXT_;
        } else {
            $db_file = _FILE_DB_PALITEXT_;
        }
        break;
    case "translate":
        $db_file = "../appdata/palicanon/translate/" . $currBook . "_translate.db3";
        break;
    case "note":
        $db_file = "../appdata/palicanon/note/" . $currBook . "_note.db3";
        break;
    case "file":
        $db_file = "../appdata/palicanon/file/file.db3";
        break;
}
//open database
//echo "$db_file";
PDO_Connect($db_file);
switch ($res_type) {
    case "wbw":
        if ($author == "templet") {
            $query = "SELECT * FROM \"main\" WHERE book = " . $PDO->quote($currBook) . " AND paragraph = " . $PDO->quote($currParagraph) . "  ORDER BY vri ";
        } else {
            $query = "SELECT * FROM \"main\" WHERE book = " . $PDO->quote($currBook) . " AND paragraph = " . $PDO->quote($currParagraph) . " AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver) . "  ORDER BY vri ";
        }
        break;
    case "heading":
        if ($author == "templet") {
            $query = "SELECT * FROM "._TABLE_PALI_TEXT_." WHERE book = " . $PDO->quote($currBook) . " AND paragraph = " . $PDO->quote($currParagraph);
        } else {
            $query = "SELECT * FROM "._TABLE_PALI_TEXT_." WHERE book = " . $PDO->quote($currBook) . " AND par_num = " . $PDO->quote($currParagraph) . " AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver);
        }
        break;
    case "translate":
        $query = "SELECT * FROM \"data\" WHERE \"paragraph\" = " . $PDO->quote($currParagraph) . " AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver);
        break;
    case "note":
        $query = "SELECT * FROM \"data\" WHERE \"paragraph\" = " . $PDO->quote($currParagraph) . " AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver);
        break;
    case "file":
        $query = "SELECT * FROM \"data\" WHERE \"paragraph\" = " . $PDO->quote($currParagraph) . " AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver);
        break;
}

$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    $outXml = "<pkg>";
    echo $outXml;
    //pakege head
    switch ($res_type) {
        case "wbw":
            $outXml = "<block>";
            $outXml .= "<info><id>$guid</id><type>$res_type</type><book>$currBook</book><paragraph>$currParagraph</paragraph><language>$language</language><author>$author</author><editor>$editor</editor><revision>$revision</revision><edition>$edition</edition><subver>$subver</subver></info>";
            $outXml .= "<data>";
            echo $outXml;
            break;
        case "heading":
            break;
        case "translate":
            $outXml = "<block>";
            $outXml .= "<info><id>$guid</id><type>$res_type</type><book>$currBook</book><paragraph>$currParagraph</paragraph><language>$language</language><author>$author</author><editor>$editor</editor><revision>$revision</revision><edition>$edition</edition><subver>$subver</subver></info>";
            $outXml .= "<data>";
            echo $outXml;
            break;
        case "note":
            $outXml = "<block>";
            $outXml .= "<info><id>$guid</id><type>$res_type</type><book>$currBook</book><paragraph>$currParagraph</paragraph><language>$language</language><author>$author</author><editor>$editor</editor><revision>$revision</revision><edition>$edition</edition><subver>$subver</subver></info>";
            $outXml .= "<data>";
            echo $outXml;
            break;
        case "file":
            $outXml = "<block>";
            $outXml .= "<info><id>$guid</id><type>$res_type</type><book>$currBook</book><paragraph>$currParagraph</paragraph><language>$language</language><author>$author</author><editor>$editor</editor><revision>$revision</revision><edition>$edition</edition><subver>$subver</subver></info>";
            $outXml .= "<data>";
            echo $outXml;
            break;

    }
    for ($i = 0; $i < $iFetch; $i++) {
        switch ($res_type) {
            case "wbw":
                $outXml = "<word>";
                $outXml = $outXml . "<id>" . $Fetch[$i]["wid"] . "</id>";
                $outXml = $outXml . "<pali>" . $Fetch[$i]["word"] . "</pali>";
                $outXml = $outXml . "<real>" . $Fetch[$i]["real"] . "</real>";
                $outXml = $outXml . "<type>" . $Fetch[$i]["type"] . "</type>";
                $outXml = $outXml . "<gramma>" . $Fetch[$i]["gramma"] . "</gramma>";
                $outXml = $outXml . "<case>" . $Fetch[$i]["type"] . "#" . $Fetch[$i]["gramma"] . "</case>";
                $outXml = $outXml . "<parent> </parent>";
                $outXml = $outXml . "<mean>" . $Fetch[$i]["mean"] . "</mean>";
                $outXml = $outXml . "<note>" . $Fetch[$i]["note"] . "</note>";
                $outXml = $outXml . "<org>" . $Fetch[$i]["part"] . "</org>";
                $outXml = $outXml . "<om>" . $Fetch[$i]["partmean"] . "</om>";
                $outXml = $outXml . "<bmc>" . $Fetch[$i]["bmc"] . "</bmc>";
                $outXml = $outXml . "<bmt>" . $Fetch[$i]["bmt"] . "</bmt>";
                $outXml = $outXml . "<un>" . $Fetch[$i]["un"] . "</un>";
                $outXml = $outXml . "<style>" . $Fetch[$i]["style"] . "</style>";
                $outXml = $outXml . "<vri>" . $Fetch[$i]["vri"] . "</vri>";
                $outXml = $outXml . "</word>";
                echo $outXml;
                break;
            case "heading":
                $outXml = "<block>";
                $outXml .= "<info><id>$guid</id><type>$res_type</type><book>$currBook</book><paragraph>$currParagraph</paragraph><language>$language</language><author>$author</author><editor>$editor</editor><revision>$revision</revision><edition>0</edition><subver>$subver</subver>";
                $outXml .= "<level>" . $Fetch[$i]["level"] . "</level>";
                $outXml .= "<style>" . $Fetch[$i]["class"] . "</style>";
                $outXml .= "</info>";
                $outXml .= "<data>";
                if ($Fetch[$i]["level"] == 0) {
                    $outXml .= "<text>" . substr($Fetch[$i]["text"], 0, 10) . "</text>";
                } else {
                    $outXml .= "<text>" . $Fetch[$i]["text"] . "</text>";
                }
                $outXml .= "</data></block>";
                echo $outXml;
                break;
            case "translate":
                $outXml = "<sen>";
                $outXml = $outXml . "<a>" . $Fetch[$i]["anchor"] . "</a>";
                $outXml = $outXml . "<text>" . $Fetch[$i]["text"] . "</text>";
                $outXml = $outXml . "</sen>";
                echo $outXml;
                break;
        }
    }
}
/*直接查询结束*/

switch ($res_type) {
    case "wbw":
        $outXml = "</data></block></pkg>";
        break;
    case "heading":
        $outXml = "</pkg>";
        break;
    case "translate":
        $outXml = "</data></block></pkg>";
        break;
    case "note":
        $outXml = "</data></block></pkg>";
        break;
    case "file":
        $outXml = "</data></block></pkg>";
        break;
}
echo $outXml;
