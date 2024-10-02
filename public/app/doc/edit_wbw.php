<?php
//工程文件操作
//建立，
require_once '../config.php';
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../public/load_lang.php";

$_book = $_POST["book"];
$_para = json_decode($_POST["para"]);
//判断单词数量 太大的不能加载
PDO_Connect(_FILE_DB_PALITEXT_);
$params = array(1, 21, 63, 171);
/*  创建一个填充了和params相同数量占位符的字符串 */
$place_holders = implode(',', array_fill(0, count($_para), '?'));

$query = "SELECT sum(lenght) FROM pali_text WHERE   paragraph IN ($place_holders) AND book = ?";
$_para[] = $_book;
$sum_len = PDO_FetchOne($query,$_para);

if($sum_len>15000){
    echo $_local->gui->oversize_to_load;
    exit;
}

# 选择channel

echo "<div class='fun_block'>";
echo "<h2>编辑逐词解析</h2>";
echo "<form action=\"{$thisFileName}\" method=\"post\">";
echo "<input type='hidden' name='op' value='{$op}'/>";
echo "<input type='hidden' name='data' value='{$data}'/>";

echo "<fieldset>";
echo "<legend>{$_local->gui->title} ({$_local->gui->required})</legend>";
echo "<div>";
echo "<input type='input' name='title' value='{$title}'/>";
echo "</div>";
echo "</fieldset>";
echo "<fieldset>";
echo "<legend>{$_local->gui->channel} ({$_local->gui->required})</legend>";
echo "<div>";
PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT * from "._TABLE_CHANNEL_." where owner_uid = ?   limit 100";
$Fetch = PDO_FetchAll($query,array($_COOKIE["user_uid"]));
$i=0;
foreach($Fetch as $row){
    echo '<div class="file_list_row" style="padding:5px;">';

    echo '<div class="pd-10"  style="max-width:2em;flex:1;">';
    echo '<input name="channal" value="'.$row["uid"].'" ';
    if($i==0){
        echo "checked";
    }
    echo ' type="radio" />';
    echo '</div>';
    echo '<div class="title" style="flex:3;padding-bottom:5px;">'.$row["name"].'</div>';
    echo '<div class="title" style="flex:3;padding-bottom:5px;">'.$row["lang"].'</div>';
    echo '<div class="title" style="flex:2;padding-bottom:5px;">';
    // 查询逐词解析库
    PDO_Connect(_FILE_DB_USER_WBW_);
	#TODO $strQueryParaList 改为预处理
    $query = "SELECT count(*) from "._TABLE_USER_WBW_BLOCK_." where channel_uid = ? and book_id=? and paragraph in {$strQueryParaList}  limit 100";
    $FetchWBW = PDO_FetchOne($query,array($row["uid"],$book));
    echo '</div>';
    echo '<div class="title" style="flex:2;padding-bottom:5px;">';
    if($FetchWBW==0){
        echo $_local->gui->blank;
        echo "<a>快捷编辑</a>";
    }
    else{
        echo $FetchWBW.$_local->gui->para;
        echo "<a href='../studio/editor.php?op=openchannal&book=$book&para={$paraList}&channal={$row["uid"]}'>open</a>";
    }
    echo '</div>';

    echo '<div class="title" style="flex:2;padding-bottom:5px;">';
    PDO_Connect(_FILE_DB_SENTENCE_,_DB_USERNAME_, _DB_PASSWORD_);
	#TODO $strQueryParaList 改为预处理

    $query = "SELECT count(*) from "._TABLE_SENTENCE_." where channel_uid = ? and book_id= ? and paragraph in {$strQueryParaList}  limit 100";
    $FetchWBW = PDO_FetchOne($query,array($row["uid"],$book));
    echo '</div>';
    echo '<div class="title" style="flex:2;padding-bottom:5px;">';
    if($FetchWBW==0){
        echo $_local->gui->blank;
    }
    else{
        echo $FetchWBW.$_local->gui->para;
    }
    echo '</div>';

    echo '<div class="summary"  style="flex:1;padding-bottom:5px;">'.$row["status"].'</div>';
    echo '<div class="author"  style="flex:1;padding-bottom:5px;">'.$row["create_time"].'</div>';
    
    echo '</div>';
    $i++;
}
echo '<div class="file_list_row" style="padding:5px;">';

echo '</div>';
echo "</div>";
echo "</fieldset>";
echo "<fieldset>";
echo "<legend>{$_local->gui->language}</legend>";
echo "<select name='lang'>";
$lang_list = new lang_enum;
foreach ($user_setting['studio.translation.lang'] as $key => $value) {
    echo "<option value='{$value}'>".$lang_list->getName($value)["name"]."</option>";
}

echo "</select>";
echo "</fieldset>";
echo "<input type=\"submit\" value='Create 建立'>";
echo "<input type='hidden' name='format' value='db'>";
echo "</form>";
echo "</div>";
?>