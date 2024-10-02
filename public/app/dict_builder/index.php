<?php
include "../config.php";
include "../public/_pdo.php";
global $PDO;

if (isset($_GET['dict_id'])) {
    $dict_id = $_GET['dict_id'];
} else {
    $dict_id = -1;
}
if (isset($_GET['page_no'])) {
    $page_no = $_GET['page_no'];
} else {
    $page_no = 0;
}
if (isset($_GET['page_size'])) {
    $page_size = $_GET['page_size'];
} else {
    $page_size = 20;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="../public/js/jquery.js"></script>
	<script src="js/dict.js"></script>
	<script src="js/index.js"></script>
	<title>PCD Studio</title>
	<style>
	#body-inner{
		display:flex;
	}
	#left_tool_bar{
		flex:2;
	    border-right: 1px solid gray;
    padding: 0 0.5em;
	}
	#build-dict{
		flex:6;
		padding: 0 0.5em;
	}
	#right-bar{
		flex:2;
	}
	.one_mean{
		display:inline-block;
		background-color:#EEE;
		padding:4px;
		margin:2px;
	}
	textarea{
		width:100%;
		height:1em;
	}
	#right_tool_bar{
		flex:3;
		position:fix;
		top:0;
		left:0;
		height:100%
	}
	#org_edit_text{
		height:8em;
	}
	.ref_word:hover{
		border-top: 1px solid #9797ff;
		border-bottom: 1px solid #9797ff;
	}
	.active{
		border-top: 1px solid #9797ff;
		border-bottom: 1px solid #9797ff;
	}

	.status_2{
		background-color:#d6d6d6;
	}
	.status_3{
		background-color:#ffc3b6;
	}
	.status_10{
		background-color:#a3f89e;
	}

	#word_org_text{
		border: 1px solid #9797ff;
		background: #ececec;
		padding: 5px 8px;
		margin: 2px 0;
	}
	#org_data h3{
		margin-bottom:5px;
	}
	.replace_table{
		height:30em;
	}

	table {
		border-collapse: collapse;
		width: 100%;
		}
		td,
		th {
			border: 1px solid #D2D2D2;
			vertical-align: baseline;

		}
		tr.h {
			background-color: #F5F5F5;
			font-weight: 500;
			color: #000000;
		}
		#message{
				background-color: #f2feff;
		border: 1px solid #bdbdbd;
		padding: 6px;
		margin: 5px;
			display:none;
		}

		#message_button{
			text-align: center;
		}
	#final_word{
	}
	#final_word_header{
		background-color:#a2de8c;
	}
	#final_word_body{
		background-color:#e7ffde;
	}
	</style>

	<script>
		var gDictId = <?php echo $dict_id ?> ;
	</script>
</head>
<body class="mainbody" id="mbody" onload="init()">
	<div id="body-inner">

			<div id="left_tool_bar" >
				<div id="right_tool_bar_title">
					<div>
						<select onchange="dict_changed(this)">
						<?php

$dictFileName = _FILE_DB_REF_;
PDO_Connect("$dictFileName");
$query = "SELECT * from info where 1  limit 0,100";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    if ($dict_id == -1) {
        echo "<option value=\"-1\" selected >选择一本字典</option>";
    }
    for ($i = 0; $i < $iFetch; $i++) {
        if ($Fetch[$i]["id"] == $dict_id) {
            $selected = "selected";
        } else {
            $selected = "";
        }
        echo "selected : $selected<br>";
        echo "<option value=\"" . $Fetch[$i]["id"] . "\" $selected >" . $Fetch[$i]["shortname"] . "</option>";
    }
}
if ($dict_id != -1) {
    $query = "SELECT count(*) from dict where dict_id = ? ";
    $dict_count = PDO_FetchOne($query, array($dict_id));
    $totle_page = ceil($dict_count / $page_size);
} else {
    $totle_page = 0;
}
?>
						</select>
					</div>
					<div id="dict_ref_search_input_div">
						<div id="dict_ref_search_input_head">
							<input id="dict_ref_search_input" type="input" onkeyup="dict_input_keyup(event,this)">
						</div>
						<div><span id="input_parts"><span></span></span></div>
					</div>
					<div id="dict_word_nav">
					<a
					<?php
if ($page_no > 0) {
    $prev_page = $page_no - 1;
    echo "href='index.php?dict_id=$dict_id&page_no=$prev_page&page_size=$page_size'";
}
?>
					>上一页</a>
					<input type="input" value="<?php echo $page_no; ?>" onchange="goto_page(this,<?php echo $dict_id; ?>,<?php echo $totle_page; ?>)" style="width:4em;" />/
					<span><?php echo $totle_page - 1; ?></span>
					<a
					<?php
if ($page_no < $totle_page) {
    $next_page = $page_no + 1;
    echo "href='index.php?dict_id=$dict_id&page_no=$next_page&page_size=$page_size'";
}
?>

					>下一页</a>
					</div>
				</div>
				<div id="left_tool_bar_inner">
					<div id="dict_ref_search">
						<div id="dict_ref_search_result">
						<?php
if ($dict_id != -1) {
    $from = $page_no * $page_size;
    $query = "SELECT id, paliword,status from dict where dict_id = ? limit ? , ? ";
    $Fetch = PDO_FetchAll($query, array($dict_id, $from, $page_size));
    foreach ($Fetch as $word) {
        $class_status = "status_" . $word["status"];
        $str_status = "";
        switch ($word["status"]) {
            case 1:
                $str_status = "";
                break;
            case 2:
                $str_status = "[草]";
                break;
            case 3:
                $str_status = "[标]";
                break;
            case 10:
                $str_status = "";
                break;
        }

        echo "<div id='word_" . $word["id"] . "' class='ref_word $class_status' onclick='res_word_selected(this," . $word["id"] . ")'>$str_status<span class='pali_word' >" . $word["paliword"] . "</div>";
    }

}
?>
						</div>
					</div>
				</div>
			</div>


		<div id="build-dict">
			<div id="message">
				<div id="message_text"></div>
				<div id="message_button"><button onclick="close_notify()">知道了</button></div>
			</div>
			<div>
			<button onclick=save(10)>保存</button>
			<button onclick=save(2)>存为草稿</button>
			<button onclick=save(3)>保存并加标记</button>

			<button onclick="refresh_word()">刷新</button>
			</div>
			<div id="org_data">
			</div>
			<div id="org_edit">
				<textarea id="org_edit_text" onkeyup="org_edit_changed()" onchange="org_edit_changed()"></textarea>
			</div>
			<div id="word_table">

			</div>
		</div>

		<div id="right-bar">
		<div id="right-bar-header">
		</div>
		<div id="right-bar-inner">
		<div>
		替换表
		<select onchange="replace_table_show(this)">
			<option value="main" selected >主表</option>
			<option value="pali"  >词头</option>
			<option value="type"  >语法</option>
			<option value="part"  >拆分</option>
		</select>
		<button onclick="save_replace_table()">保存</button>
		</div>
		<textarea id="replace_table_main" class="replace_table" ></textarea>
		<textarea id="replace_table_pali" class="replace_table" style="display:none;"></textarea>
		<textarea id="replace_table_type" class="replace_table" style="display:none;"></textarea>
		<textarea id="replace_table_part" class="replace_table" style="display:none;"></textarea>

		</div>

		</div>
	</div>

</body>
</html>