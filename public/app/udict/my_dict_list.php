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
	#setting_user_dict_nav{
		width:95%;
		display:inline-flex;
		justify-content: space-between;
}
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

require_once "../config.php";
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
    echo "<div>{$_local->gui->search}<span style='display:inline-block;width:20em;'><input type='input'  /></span></div>";
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
    <div id="setting_user_dict_nav">
    <?php
if ($iCurrPage == 0) {
        echo "<span>{$_local->gui->first_page}</span>";
        echo "<span>{$_local->gui->previous_page}</span>";
    } else {
        echo "<span><a href=\"../udict/my_dict_list.php?page=0\">{$_local->gui->first_page}</a></span>";
        $prevPage = $iCurrPage - 1;
        echo "<span><a href=\"../udict/my_dict_list.php?page={$prevPage}\">{$_local->gui->previous_page}</a></span>";
    }

    echo "<span style='display:inline-block;white-space: nowrap;'>{$_local->gui->page_num}<input type=\"input\" value=\"" . ($iCurrPage + 1) . "\" style='width:3em;height:1em;'/>/{$iPages}</span>";

    if ($iCurrPage < $iPages - 1) {
        echo "<span><a href=\"../udict/my_dict_list.php?page=" . ($iCurrPage + 1) . "\">{$_local->gui->next_page}</a></span>";
        echo "<span><a href=\"../udict/my_dict_list.php?page=" . ($iPages - 1) . "\">{$_local->gui->last_page}</a></span>";

    } else {
        echo "<span>{$_local->gui->next_page}</span><span>{$_local->gui->last_page}</span>";
    }
    echo "<span id='setting_user_dict_count'>{$_local->gui->vocabulary}：{$iCountWords}</span>";
    ?>
    </div>
    <table style="width:95%;">
        <tr style="padding:5px;font-weight: bold;">
            <td style=''><input type="checkbox" /></td>
            <td style=''><?php echo $_local->gui->spell; //拼写?></td>
            <td style=''><?php echo $_local->gui->wordtype; //单词类型?></td>
            <td style=''><?php echo $_local->gui->gramma; //语法?></td>
            <td style=''><?php echo $_local->gui->g_mean; //意思?></td>
            <td style=''><?php echo $_local->gui->parent; //语基?></td>
            <td style=''><?php echo $_local->gui->dictsouce; //词条来源?></td>
            <td style=''><?php echo $_local->gui->citations; //引用数?></td>
            <td style=''></td>
        </tr>
    <?php
foreach ($allWords as $word) {
        echo '<tr class="file_list_row" style="padding:5px;display: table-row;">';
        echo "<td style=''><input type=\"checkbox\" /></td>";
        echo "<td style='max-width:12vw; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>{$word["pali"]}</td>";
        echo "<td style=''>".$word["type"]."</td>";
        echo "<td style=''>{$word["gramma"]}</td>";
        echo "<td style='max-width:12vw; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>{$word["mean"]}</td>";
        echo "<td style='max-width:12vw; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>{$word["parent"]}</td>";
        if ($word["creator"] == $UID) {
            echo "<td style=''>{$_local->gui->original}</td>";
        } else {
            echo "<td style=''>{$_local->gui->reference}</td>";
        }
        echo "<td style=''>{$word["ref_counter"]}</td>";
        echo "<td style=''><button>删除</button></td>";
        echo "</tr>";
    }
}
?>
    </table>


<?php
require_once '../studio/index_foot.php';
?>