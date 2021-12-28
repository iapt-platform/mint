<?php
include "./config.php";
include "./_pdo.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<style>
	.word {
    width: Auto;
    float: left;
    margin:0 0.5em 1.8em 0;
}
	.pali{
    font-weight: 500;
    font-size: 110%;
    padding: 0px 2px;
    margin: 0px;
    font-weight: 500;
}
	.mean {
    font-weight: 500;
	padding: 0px 2px;
    margin: 0px;
	}
	.org, .om, .case {
    font-size: 80%;
    margin: 0.3em 0;
    clear: both;
	margin:0;
}
.cls{
	clear:both;
}

h1{
margin-left:0em;
}
h2{
margin-left:1em;
}

h3{
margin-left:2em;
}

h4{
margin-left:3em;
}

h5{
margin-left:4em;
}

h6{
margin-left:5em;
}

h7{
margin-left:6em;
}

h8{
margin-left:7em;
}


	</style>
<script>
function mOver(obj)
{
obj.style.color="red"
}

function mOut(obj)
{
obj.style.color="black"
}
</script>
</head>
<body>
<?php
$get_res_type = $_GET["res_type"];
$get_book = $_GET["book"];
$get_par_begin = $_GET["begin"];
$get_par_end = $_GET["end"];
$language = $_GET["language"];
$author = $_GET["author"];
$editor = $_GET["editor"];
$revision = $_GET["revision"];
$edition = $_GET["edition"];
$subver = $_GET["subver"];

echo "Type:$get_res_type<br />";
echo "book:$get_book<br />";
echo "par_begin:$get_par_begin<br />";
echo "par_end:$get_par_end<br />";
echo "language:$language<br />";
echo "author:$author<br />";
echo "editor:$editor<br />";

$outHtml = "";
if ($author == "templet") {
    switch ($get_res_type) {
        case "wbw":
            $db_file = "../appdata/palicanon/templet/" . $get_book . "_tpl.db3";
            break;
        case "heading":
            //$db_file = "../appdata/palicanon/templet/toc.db3";
            $db_file = "../appdata/palicanon/pali_text/" . $get_book . "_pali.db3";
            break;
    }
} else {
    switch ($get_res_type) {
        case "heading":
            $db_file = "../appdata/palicanon/heading/toc.db3";
            break;
        default:
            $db_file = "../appdata/palicanon/" . $get_res_type . "/" . $get_book . "_" . $get_res_type . ".db3";
            break;
    }
}

//open database
PDO_Connect("$db_file");

switch ($get_res_type) {
    case "translate":
    case "note":
        $query = "SELECT * FROM \"data\" WHERE (\"paragraph\" BETWEEN " . $PDO->quote($get_par_begin) . " AND " . $PDO->quote($get_par_end) . ") AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver);
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                switch ($get_res_type) {
                    case "translate":
                        $outHtml = "<p>" . $Fetch[$i]["text"] . "</p>";
                        break;
                    case "note":
                        $outHtml = "<p>" . $Fetch[$i]["text"] . "</p>";
                        break;
                        break;
                }
                echo $outHtml;
            }
        }
        break;
    case "heading":
        if ($author == "templet") {
            $query = "SELECT * FROM \"data\" WHERE \"book\" = " . $PDO->quote($get_book) . " AND (\"paragraph\" BETWEEN " . $PDO->quote($get_par_begin) . " AND " . $PDO->quote($get_par_end) . ") AND \"level\">'0'";
        } else {
            $query = "SELECT * FROM \"data\" WHERE book=" . $PDO->quote($get_book) . " AND (\"paragraph\" BETWEEN " . $PDO->quote($get_par_begin) . " AND " . $PDO->quote($get_par_end) . ") AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition);
        }
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $level = $Fetch[$i]["level"];
                $outHtml = "<h$level>" . $Fetch[$i]["text"] . "</h$level>";
                echo $outHtml;
            }
        }
        break;

    case "wbw":
        for ($iPar = $get_par_begin; $iPar <= $get_par_end; $iPar++) {
            if ($author == "templet") {
                $query = "SELECT * FROM \"main\" WHERE (\"paragraph\" = " . $PDO->quote($iPar) . " ) ";
            } else {
                $query = "SELECT * FROM \"main\" WHERE (\"paragraph\" = " . $PDO->quote($iPar) . " ) AND \"language\" = " . $PDO->quote($language) . " AND \"author\" = " . $PDO->quote($author) . " AND \"editor\" = " . $PDO->quote($editor) . " AND \"edition\" = " . $PDO->quote($edition) . " AND \"subver\" = " . $PDO->quote($subver);
            }
            echo "<h2>$iPar</h2>";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo "<div class='wbw_par'>";
                for ($i = 0; $i < $iFetch; $i++) {
                    $outHtml = "<div class='word' onmouseover=\"mOver(this)\" onmouseout=\"mOut(this)\" >";
                    $outHtml .= "<p class='pali'>" . $Fetch[$i]["word"] . "</p>";
                    $outHtml .= "<p class='mean'>" . $Fetch[$i]["mean"] . "</p>";
                    $outHtml .= "<p class='org'>" . $Fetch[$i]["part"] . "</p>";
                    $outHtml .= "<p class='om'>" . $Fetch[$i]["partmean"] . "</p>";
                    $outHtml .= "<p class='case'>" . $Fetch[$i]["type"] . "#" . $Fetch[$i]["gramma"] . "</p>";
                    $outHtml .= "</div>";
                    echo $outHtml;
                }

                echo "<div class='cls'></div>";
                echo "</div>";
            }

        }
        break;
}
/*查询结束*/

?>

</body>
</html>