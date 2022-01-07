<?php
/*
加载巴利原文
 */
include __DIR__."/../config.php";
include __DIR__."/../public/_pdo.php";

$get_book = $_GET["book"];
if (substr($get_book, 0, 1) == "p") {
    $get_book = substr($get_book, 1);
}

echo "book:$get_book<br />";

//open database
PDO_Connect(_FILE_DB_PALITEXT_);

$query = "SELECT paragraph,html FROM "._TABLE_PALI_TEXT_." WHERE book = ? order by paragraph asc";
$Fetch = PDO_FetchAll($query, array($get_book));
$iFetch = count($Fetch);
if ($iFetch > 0) {
    for ($i = 0; $i < $iFetch; $i++) {
        $parNumber = $Fetch[$i]["paragraph"];
        echo "<div id=\"wizard_pali_par_$parNumber\" class=\"wizard_par_div\">";
        echo "<div class=\"wizard_par_tools\">";
        echo "<div class=\"wizard_par_tools_title\">";
        echo "<input id='par_enable_$parNumber' onclick='par_enable_change($parNumber,this)' type=\"checkbox\" checked/>";
        echo "<a href='../reader/?view=chapter&book={$get_book}&para={$parNumber}' name='pali_text_par_$parNumber' target='_blank'>$parNumber</a><span id='par_level_$parNumber' class='par_level'></span>";
        echo "</div>";
        echo "</div>";
        echo "<div id=\"wizard_pali_par_text_$parNumber\">" . $Fetch[$i]["html"] . "</div>";
        echo "</div>";
    }
}
/*结束*/
