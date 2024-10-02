<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		.toc_1{
			padding-left:0;
		}
		.toc_2{
			padding-left:1em;
		}
		.toc_3{
			padding-left:2em;
		}
		.toc_4{
			padding-left:3em;
		}

	</style>
</head>
<body>
<?php
include "../app/config.php";
include "../app/_pdo.php";

$toc = array();

if (($handle = fopen("../appdata/book_index.csv", 'r')) !== false) {
    while (($data = fgetcsv($handle, 0, ',')) !== false) {
        array_push($toc, $data);
        if ($data[2] > 0) {
            $bookid = 'p' . $data[2];
            $toc_book[$bookid] = $data[1];
        }
    }
}

echo "<h2>Word Analysis</h2>";

if (!isset($_GET["col"])) {

    ?>
<form action="pc_word_analysis.php" method="get">
<input type="submit">
order by: <input type="text" name="col" value="parent" /><br>
<?php
$arrlength = count($toc);

    echo "<ul>";
    for ($x = 0; $x < $arrlength; $x++) {
        echo "<li class='toc_" . $toc[$x][0] . "'><input type='checkbox' name='p" . $toc[$x][2] . "' onchange=\"chk_change(this)\" />" . $toc[$x][1] . "</li>";
    }
    echo "</ul>";
    ?>
<input type="submit">
</form>
<?php
return;
}

$col = $_GET["col"];
//$get_book=$_GET["book"];
//$boolList=str_getcsv($get_book,',');/*生成書名數組*/
echo "<a href='pc_word_analysis.php'>返回</a><br>";
echo "您选择的是：</br>";

$boolList = array();
for ($i = 1; $i < 218; $i++) {
    if (isset($_GET["p$i"])) {
        if ($_GET["p$i"] == "on") {
            array_push($boolList, "p$i");
            echo $toc_book["p$i"] . "; ";
        }
    }
}
echo "</br>";

$countInsert = 0;
$wordlist = array();

$bookstring = "";
for ($i = 0; $i < count($boolList); $i++) {
    $bookstring .= "'" . $boolList[$i] . "'";
    if ($i < count($boolList) - 1) {
        $bookstring .= ",";
    }
}

//open database
PDO_Connect(_FILE_DB_STATISTICS_);
if ($col == "parent") {
    $query = "SELECT count(*) FROM "._TABLE_WORD_STATISTICS_." WHERE (bookid in (" . $bookstring . ")) "; /*查總數，并分類匯總*/
    $count_word = PDO_FetchOne($query);
    $query = "SELECT sum(count) FROM "._TABLE_WORD_STATISTICS_." WHERE (bookid in (" . $bookstring . ")) "; /*查總數，并分類匯總*/
    $sum_word = PDO_FetchOne($query);
    $query = "select count(*) from (SELECT count() FROM "._TABLE_WORD_STATISTICS_." WHERE (bookid in (" . $bookstring . ") and parent<>'') group by parent )"; /*查總數，并分類匯總*/
    $count_parent = PDO_FetchOne($query);
    $query = "SELECT sum(count) FROM "._TABLE_WORD_STATISTICS_." WHERE (bookid in (" . $bookstring . ") and  parent<>'') "; /*查總數，并分類匯總*/
    $sum_parent = PDO_FetchOne($query);
    echo "单词个数： $count_word<br>总词数： $sum_word<br> parent个数： $count_parent<br> 有parent的单词总数:$sum_parent <br>";
    $query = "select * from (SELECT parent,sum(count) as wordsum FROM "._TABLE_WORD_STATISTICS_." WHERE (bookid in (" . $bookstring . ") and parent<>'') group by parent) order by wordsum DESC limit 0 ,2000"; /*查總數，并分類匯總*/
    $Fetch = PDO_FetchAll($query);
    $iFetch = count($Fetch);
    echo "<table>";
    echo "<th>序号</th><th>parent</th><th>数量</th><th>百分比</th><th>累计百分比</th>";
    $sum_prsent = 0;
    if ($iFetch > 0) {
        $sum = $Fetch[0]["wordsum"];
        $first = $sum * 100 / $sum_word;

        for ($i = 0; $i < $iFetch; $i++) {
            $sum = $Fetch[$i]["wordsum"];
            $prsent = $sum * 100 / $sum_word;
            $prsent1 = $prsent * 100 / $first;
            $sum_prsent += $prsent;
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td><td>" . $Fetch[$i]["parent"] . "</td><td>" . $sum . "</td><td><span style='width:" . $prsent1 . "px;background-color:red;'></span><span style:'width:" . (100 - $prsent1) . "px;background-color: var(--tool-link-hover-color);'></span>" . $prsent . "</td><td>" . $sum_prsent . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {

}

/*查询结束*/

?>

</body>
</html>