<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="course_list()">

	<script >
	var gCurrPage="udict";
	</script>

	<style>
	#udict {
		background-color: var(--btn-border-color);

	}
	#udict:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
    }
    #word_list{
        width:unset;
    }
	</style>

	<?php
require_once '../studio/index_tool_bar.php';
?>

	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div id="word_list"  class="file_list_block">

		<div class="tool_bar">
	<div>
	<?php echo $_local->gui->userdict; ?>
	</div>

	<div>
		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->add; ?></span>
			<button id="file_add" type="button" class="icon_btn" title=" ">
				<a href="../course/my_channal_new.php">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
				</svg>
				</a>
			</button>
		</span>

		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin; ?></span>
			<button id="to_recycle" type="button" class="icon_btn" onclick="file_del()" title=" ">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
				</svg>
			</button>
		</span>
	</div>

</div>

<div id="userfilelist">
<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../ucenter/function.php';

if (isset($_GET["page"])) {
    $iCurrPage = $_GET["page"];
} else {
    $iCurrPage = 0;
}
$iOnePage = 300;

PDO_Connect(_FILE_DB_WBW_);
$query = "select count(word_index) as co  from user_index where user_id={$UID}";
$allWord = PDO_FetchOne($query);
$iCountWords = $allWord;

if ($iCountWords == 0) {
    echo "<div id='setting_user_dict_count'>您的用户字典中没有单词。</div>";
} else {
    echo "<div>search:<span style='display:inline-block;width:20em;'><input type='input'  /></span></div>";
    $iPages = ceil($iCountWords / $iOnePage);
    if ($iCurrPage > $iPages) {
        $iCurrPage = $iPages;
    }
    $begin = $iCurrPage * $iOnePage;
    $query = "select word_index  from user_index where user_id={$UID} order by id DESC limit {$begin},{$iOnePage} ";

    $allWord = PDO_FetchAll($query);
    $strQuery = "('";
    foreach ($allWord as $one) {
        $strQuery .= $one["word_index"] . "','";
    }
    $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
    $strQuery .= ")";
    $query = "select *  from dict where id in {$strQuery} order by time DESC";
    $allWords = PDO_FetchAll($query);
    ?>
    <div id="setting_user_dict_nav" style="backgroud-color:gray">
    <?php
if ($iCurrPage == 0) {
        echo "第一页 | ";
        echo "上一页";
    } else {
        echo "<a href=\"../udict/my_dict_list.phpphp?page=0\">第一页</a>";
        $prevPage = $iCurrPage - 1;
        echo "<a href=\"../udict/my_dict_list.php?page={$prevPage}\">上一页</a>";
    }

    echo "第<span style='display:inline-block;width:4em;'><input type=\"input\" value=\"" . ($iCurrPage + 1) . "\" size=\"4\" /></span>页";
    echo "共{$iPages}页";

    if ($iCurrPage < $iPages - 1) {
        echo "<a href=\"../udict/my_dict_list.php?page=" . ($iCurrPage + 1) . "\">下一页</a>";
        echo "<a href=\"../udict/my_dict_list.php?page=" . ($iPages - 1) . "\">最后一页</a>";

    } else {
        echo "下一页 | 最后一页";
    }
    echo "<span id='setting_user_dict_count'>总计{$iCountWords}</span>";
    ?>
    </div>
    <div>
        <div style="display:flex;">
            <div><input type="checkbox" /></div>
            <div>拼写</div>
            <div>类型</div>
            <div>语法</div>
            <div>意思</div>
            <div>语基</div>
            <div>状态</div>
            <div>引用</div>
            <div></div>
        </div>
    <?php
foreach ($allWords as $word) {
        echo '<div class="file_list_row" style="padding:5px;">';
        echo "<div style='flex:1;'><input type=\"checkbox\" /></div>";
        echo "<div style='flex:3;'>{$word["pali"]}</div>";
        echo "<div style='flex:1;'>{$word["type"]}</div>";
        echo "<div style='flex:1;'>{$word["gramma"]}</div>";
        echo "<div style='flex:3;'>{$word["mean"]}</div>";
        echo "<div style='flex:3;'>{$word["parent"]}</div>";
        if ($word["creator"] == $UID) {
            echo "<div style='flex:1;'>原创</div>";
        } else {
            echo "<div style='flex:1;'>引用</div>";
        }
        echo "<div style='flex:1;'>{$word["ref_counter"]}</div>";
        echo "<div style='width:1em;;'>...</div>";
        echo "</div>";
    }
}
?>
    </div>


<?php
require_once '../studio/index_foot.php';
?>