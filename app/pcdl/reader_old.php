<?php
require_once "../public/_pdo.php";
require_once "../config.php";

if (isset($_GET["album"])) {
    $album = $_GET["album"];
}

if (isset($_GET["book"])) {
    $book = $_GET["book"];
} else {
    echo "no book id";
    exit;
}
if (substr($book, 0, 1) == 'p') {
    $book = substr($book, 1);
}
if (isset($_GET["paragraph"])) {
    $paragraph = $_GET["paragraph"];
} else {
    $paragraph = -1;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/reader.css"/>
	<title>PCD Reader</title>
	<style>
	.word{
		display:inline-block;
		padding: 1px 3px;
	}
	.mean{
		font-size: 65%;
	}
		/* 下拉内容 (默认隐藏) */
	#mean_menu {
		margin: 0.3em;
		position: absolute;
		background-color: white;
		min-width: 8em;
		max-width: 30em;
		margin: -1px 0px;
		box-shadow: 0px 3px 13px 0px black;
		color: var(--main-color);
		z-index: 200;
	}

	/* 下拉菜单的链接 */
	#mean_menu a {
		/*padding: 0.3em 0.4em;*/
		line-height: 160%;
		text-decoration: none;
		display: block;
		cursor: pointer;
		text-align: left;
		font-size:80%;
	}

	/* 鼠标移上去后修改下拉菜单链接颜色 */
	.mean_menu a:hover {
		background-color: blue;
		color: white;
	}

	</style>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script src="js/reader.js"></script>

	<script src="../appdata/dict/3rd/bh.js"></script>
	<script src="../appdata/dict/system/sys_regular.js"></script>
	<script>
		var curr_tool="";
		var dighest_count=0;//书摘段落数量
		var res_list=new Array();
		var new_comments_album=-1;
		var new_comments_book=-1;
		var new_comments_paragraph=-1;
		function add_new_res(album,book,paragraph,text){
			var new_res=new Object();
			new_res.album=album;
			new_res.book=book;
			new_res.paragraph=paragraph;
			new_res.dighest=false;
			new_res.text=text;
			new_res.textchanged=false;
			res_list.push(new_res);
		}
		function tool_changed(tool_name){
			if(tool_name==curr_tool){
				return;
			}
			$("#main_tool_bar").fadeOut();
			curr_tool=tool_name;
			switch(tool_name){
				case "comments":
					$("#tool_bar_dighest").fadeOut();
					$("#tool_bar_comments").fadeIn();
				break;
				case "dighest":
					$("#tool_bar_comments").fadeOut();
					$("#tool_bar_dighest").fadeIn();
				break;
				case "fix":
					$("#tool_bar_comments").fadeOut();
					$("#tool_bar_fix").fadeIn();
					render_all_tran();
				break;
			}

		}
		function paragraph_click(album,book,paragraph){
			switch(curr_tool){
				case "comments":
					new_comments(album,book,paragraph);
				break;
				case "dighest":
					dighest_par_click(album,book,paragraph);
				break;
			}
		}
		function new_comments(album,book,paragraph){
			new_comments_album=album;
			new_comments_book=book;
			new_comments_paragraph=paragraph;
			document.getElementById("new-comm-a"+album+"-b"+book+"-"+paragraph).appendChild(document.getElementById("new_comm_div"));

		}
		function new_comm_cancel(){
			$("#tool_bar_comments").fadeOut();
			$("#main_tool_bar").fadeIn();
			document.getElementById("new_comm_text").value="";
			document.getElementById("new_comm_shell").appendChild(document.getElementById("new_comm_div"));
			curr_tool="";
		}
		function new_comm_submit(){
			$("#tool_bar_comments").fadeOut();
			$("#main_tool_bar").fadeIn();
			curr_tool="";
			var comm_text=document.getElementById("new_comm_text").value;
			$.post("comments.php",
			{
				album:new_comments_album,
				book:new_comments_book,
				paragraph:new_comments_paragraph,
				text:comm_text
			},
			function(data,status){
				alert("Data: " + data + "\nStatus: " + status);
			});
		}

		//书摘处理
		function dighest_par_click(album,book,paragraph){
			for (var x in res_list){
				if(
				res_list[x].album==album &&
				res_list[x].book==book &&
				res_list[x].paragraph==paragraph){
					if(res_list[x].dighest==false){
						res_list[x].dighest=true;
						res_list[x].text=document.getElementById("text-a"+album+"-b"+book+"-"+paragraph).innerHTML;
						$("#text-a"+album+"-b"+book+"-"+paragraph).css("background-color","yellow");
						dighest_count++;
					}
					else{
						res_list[x].dighest=false;
						$("#text-a"+album+"-b"+book+"-"+paragraph).css("background-color","white");
						dighest_count--;
					}
				}
			}
			$("#dighest_message").text="已经选择"+dighest_count+"段";
		}

		//将段落列表重置
		function dighest_reset_res_list(){
			for (var x in res_list){
				var album=res_list[x].album;
				var book=res_list[x].book;
				var paragraph=res_list[x].paragraph;

					if(res_list[x].dighest==false){
						dighest_count=0;
					}
					else{
						res_list[x].dighest=false;
						$("#text-a"+album+"-b"+book+"-"+paragraph).css("background-color","white");
						dighest_count=0;
					}

			}
		}

		function dighest_cancle(){
			$("#tool_bar_dighest").fadeOut();
			$("#main_tool_bar").fadeIn();
			curr_tool="";
			//将段落列表重置
			dighest_reset_res_list();
		}
		function dighest_ok(){
			var output="";
			for (var x in res_list){
				if(res_list[x].dighest==true){
					output+="<p>"+res_list[x].text+"</p>";
				}
			}
			//书摘文字预览
			document.getElementById("dighest_text_preview").innerHTML=output;
			$("#tool_bar_dighest").fadeOut();
			$("#dighest_edit_div").fadeIn();

		}

		function dighest_edit_cancle(){
			$("#dighest_edit_div").fadeOut();
			$("#main_tool_bar").fadeIn();
			curr_tool="";
			//将段落列表重置
			dighest_reset_res_list();
		}
		function dighest_edit_submit(){
			$("#dighest_edit_div").fadeOut();
			$("#main_tool_bar").fadeIn();
			curr_tool="";

			//计算书摘数量 生成书摘字符串
			if(res_list.length==0){
				return;
			}
			var output=new Array();
			for (var x in res_list){
				if(res_list[x].dighest==true){
					output.push(res_list[x].album+"-"+res_list[x].book+"-"+res_list[x].paragraph);
				}
			}

			var dighest_text=output.join();
			var dighest_title=document.getElementById("dighest_edit_title").value;
			var dighest_summary=document.getElementById("dighest_edit_summary").value;
			var dighest_tag=document.getElementById("dighest_edit_taget").value;
			if(dighest_title==""){alert("标题不能为空");return;}
			if(dighest_summary==""){alert("简介不能为空");return;}
			if(dighest_tag==""){alert("标签不能为空");return;}
			$.post("dighest.php",
			{
				title:dighest_title,
				summary:dighest_summary,
				tag:dighest_tag,
				data:dighest_text
			},
			function(data,status){
				alert("Data: " + data + "\nStatus: " + status);
			});
			//将段落列表重置
			dighest_reset_res_list();
		}

		function setNaviVisibility(){
			var objNave = document.getElementById('leftmenuinner');
			var objblack = document.getElementById('BV');
			if ( objNave.className=='viewswitch_off'){
				objblack.style.display = "block";
				objNave.className = "viewswitch_on";
			}
			else{
				objblack.style.display = "none";
				objNave.className = "viewswitch_off";
			}
		}

		function render_all_tran(mode="fix"){
			for (var x in res_list){
				var album=res_list[x].album;
				var book=res_list[x].book;
				var paragraph=res_list[x].paragraph;
				var text=res_list[x].text;
				if(mode=="fix"){
					var new_text=getSuperTranslateModifyString(x);
				}
				else{
					var new_text=text;
				}
				var obj=document.getElementById("text-a"+album+"-b"+book+"-"+paragraph);
				if(obj){
					obj.innerHTML=new_text;
				}
			}
		}
		function getSuperTranslateModifyString(index){
			var newString = res_list[index].text.replace(/。/g,"。#");
			newString = newString.replace(/，/g,"，#");
			newString = newString.replace(/！/g,"！#");
			newString = newString.replace(/？/g,"？#");
			newString = newString.replace(/”/g,"”#");
			newString = newString.replace(/“/g,"“#");
			newString = newString.replace(/’/g,"’#");

			arrString = newString.split("#");

			var output="";
			var str_pos=0;
			for (x in arrString){
				var str_len=arrString[x].length;
				str_pos+=str_len;
				output +=arrString[x]+"<span  class=\"tooltip\">※<span class=\"tooltiptext tooltip-bottom\"><button onclick='text_move("+index+"," + str_pos + ",0)'>▲</button> <button onclick='text_move("+index+"," + str_pos + ",1)'>▼</button> </span> </span> ";
			}
			return output;
		}

		function text_move(index,str_pos,updown){
			if(updown==0 && index==0){
				return;
			}
			if(updown==1 && index==res_list.length-1){
				return;
			}
			if(updown==0){
				res_list[index-1].text+=res_list[index].text.substring(0,str_pos);
				res_list[index-1].textchanged=true;
				res_list[index].text=res_list[index].text.substring(str_pos);
				res_list[index].textchanged=true;

			}
			else{
				res_list[index+1].text=res_list[index].text.substring(str_pos)+res_list[index+1].text;
				res_list[index+1].textchanged=true;
				res_list[index].text=res_list[index].text.substring(0,str_pos);
				res_list[index].textchanged=true;
			}
			render_all_tran();
		}

		function fix_cancle(){
			$("#tool_bar_fix").fadeOut();
			$("#main_tool_bar").fadeIn();
			curr_tool="";
			render_all_tran("");
		}
		function fix_ok(){
			$("#tool_bar_fix").fadeOut();
			$("#main_tool_bar").fadeIn();
			curr_tool="";
			render_all_tran("");

			//计算书摘数量 生成书摘字符串
			if(res_list.length==0){
				return;
			}
			var output=new Array();
			for (var x in res_list){
				if(res_list[x].textchanged==true){
					output.push(res_list[x].album+"@"+res_list[x].book+"@"+res_list[x].paragraph+"@"+res_list[x].text);
					res_list[x].textchanged=false;
				}
			}

			var fix_text=output.join("#");
			var fix_album=res_list[0].album;
			$.post("tran_text.php",
			{
				album:fix_album,
				data:fix_text
			},
			function(data,status){
				alert("Data: " + data + "\nStatus: " + status);
			});
		}

		function lookup(){
			var xPali=document.getElementsByClassName("pali");
			for (var x in xPali){
				var pali = xPali[x].innerHTML;
				var xMean=xPali[x].nextSibling;
				if(bh[pali]){
					var arrMean=bh[pali].split("$");
					if(arrMean.length>0){
					xMean.innerHTML=arrMean[0];
					}
				}
				else if(sys_r[pali]){
					var word_parent=sys_r[pali];
					if(bh[word_parent]){
						var arrMean=bh[word_parent].split("$");
						if(arrMean.length>0){
							xMean.innerHTML=arrMean[0];
						}
					}
				}
			}

		}
	</script>
<body class="reader_body" onload="">
		<!-- tool bar begin-->
		<div id="main_tool_bar" class='reader_toolbar'>
			<div id="index_nav">
				<button onclick="setNaviVisibility()">M</button>
			</div>
			<div>
				<span id="tool_bar_title">Title</span>
			</div>
			<div>
				<a>☆★</a>
				<div class="case_dropdown">
					<p class="case_dropbtn"><button>A</button></p>
					<div class="case_dropdown-content" style="width:25em;">
						<div ><button>A+</button><button>A-</button></div>
						<div ><button>白</button><button>棕</button><button>夜s</button></div>
					</div>
				</div>
				<div class="case_dropdown">
					<p class="case_dropbtn"><button>┇</button></p>
					<div class="case_dropdown-content">
						<a onclick="tool_changed('dighest')">书摘</a>
						<a onclick="tool_changed('comments')">批注</a>
						<a onclick="tool_changed('target')">标签</a>
						<a onclick="tool_changed('layout')">布局</a>
						<a onclick="tool_changed('porpername')">术语</a>
						<a onclick="tool_changed('share')">分享</a>
						<a onclick="tool_changed('fix')">修改</a>
					</div>
				</div>

			</div>
		</div>

		<div id="tool_bar_comments">
			<div class='reader_toolbar' style="height:auto;">
			<div>
			</div>
			<div>
				<span>单击段落文字添加批注</span>
			</div>
			<div>
			</div>
			</div>
		</div>
		<div id="tool_bar_dighest">
			<div  class='reader_toolbar'  style="height:auto;">
			<div>
				<button onclick="dighest_cancle()">取消</button>
			</div>
			<div>
				<span id="dighest_message">单击文字选择段落</span>
			</div>
			<div>
				<button onclick="dighest_ok()">完成</button>
			</div>
			</div>
		</div>

		<div id="tool_bar_fix">
			<div  class='reader_toolbar'  style="height:auto;">
			<div>
				<button onclick="fix_cancle()">取消</button>
			</div>
			<div>
				<span id="fix_message">单击按钮调整段落</span>
			</div>
			<div>
				<button onclick="fix_ok()">完成</button>
			</div>
			</div>
		</div>
		<!--tool bar end -->

		<div id="main_text_view" style="padding-bottom: 10em;">
<?php

PDO_Connect( _FILE_DB_RESRES_INDEX_);
if (isset($album)) {
    //更新点击
    $query = "select * from 'index' where book='$book' and paragraph='$paragraph' and album=$album";
    $FetchRes = PDO_FetchAll($query);
    if (count($FetchRes) > 0) {
        $id = $FetchRes[0]["id"];
        $hit = $FetchRes[0]["hit"] + 1;
        $query = "UPDATE 'index' SET hit = $hit WHERE id = $id";
        $stmt = @PDO_Execute($query);
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            print_r($error[2]);
        }
    }
}
//获取段落信息 如 父段落 下一个段落等
$query = "select * from 'paragraph_info' where book='$book' and paragraph='$paragraph'";
$FetchParInfo = PDO_FetchAll($query);
if (!$FetchParInfo) {
    echo "Error:no paragraph info";
}
$par_begin = $paragraph + 1 - 1;
$par_end = $par_begin + $FetchParInfo[0]["length"] - 1;
$par_next = $FetchParInfo[0]["next"];
//生成一个段落空壳 等会儿查询数据，按照不同数据类型填充进去
for ($iPar = $par_begin; $iPar <= $par_end; $iPar++) {
    echo "<div id='par-b$book-$iPar' class='par_div'>";
    echo "<div id='par-pali-b$book-$iPar' class='par_pali_div'>";
    echo "</div>";
    echo "<div id='par-wbwdiv-b$book-$iPar' class='par_translate_div'>";
    echo "</div>";
    echo "<div id='par-translate-b$book-$iPar' class='par_translate_div'>";
    echo "</div>";
    echo "<div id='par-note-b$book-$iPar' class='par_translate_div'>";
    echo "</div>";
    echo "</div>";
}

//先查pali text 因为要获取段落标题级别

PDO_Connect(_FILE_DB_PALITEXT_);

if ($par_begin == -1) {
    $query = "SELECT * FROM \"pali_text\" WHERE book = '{$book}' ";
} else {
    $query = "SELECT * FROM \"pali_text\" WHERE book = '{$book}' and  (\"paragraph\" BETWEEN " . $PDO->quote($par_begin) . " AND " . $PDO->quote($par_end) . ") ";
}
//查询pali text内容
$FetchText = PDO_FetchAll($query);
$iFetchText = count($FetchText);
if ($iFetchText > 0) {
    for ($i = 0; $i < $iFetchText; $i++) {
        $currParNo = $FetchText[$i]["paragraph"];
        $currParLevel = $FetchText[$i]["level"];
        $par_level["$currParNo"] = $currParLevel;
        echo "<div id='par-palitext-b{$book}-{$currParNo}' class='palitext_text'><div class=\"text_level_{$currParLevel}\">" . $FetchText[$i]["text"] . "</div></div>";
        echo "<script>";
        echo "document.getElementById('par-pali-b{$book}-{$currParNo}').appendChild(document.getElementById('par-palitext-b{$book}-{$currParNo}'));";
        echo "</script>";
    }

}
//巴利原文加载结束
if (isset($album)) {

    /*
    //自动逐词译
    $db_file = "../appdata/palicanon/templet/p".$book."_tpl.db3";
    PDO_Connect("$db_file");
    for($iPar=$par_begin;$iPar<=$par_end;$iPar++){
    $query="SELECT * FROM \"main\" WHERE (\"paragraph\" = ".$PDO->quote($iPar)." ) ";
    $Fetch = PDO_FetchAll($query);
    $iFetch=count($Fetch);
    if($iFetch>0){
    echo "<div id='par-wbw-b$book-$iPar' class='wbw_par'>";
    for($i=0;$i<$iFetch;$i++){
    $type=$Fetch[$i]["type"];
    if($type!=".ctl."){
    echo "<div class='word'>";
    echo "<div class='pali'>".$Fetch[$i]["word"]."</div>";
    echo "<div class='mean'>".$Fetch[$i]["mean"]."</div>";
    echo "</div>";
    }
    }
    echo "</div>";
    echo "<script>";
    echo "document.getElementById('par-wbwdiv-b$book-$iPar').appendChild(document.getElementById('par-wbw-b$book-$iPar'));";
    echo "</script>";

    }
    }
    ////自动逐词译结束
     */

    PDO_Connect(_FILE_DB_RESRES_INDEX_);
    $query = "select * from 'album' where id='$album'";
    $Fetch = PDO_FetchAll($query);
    $iFetch = count($Fetch);
    if ($iFetch > 0) {
        switch ($Fetch[0]["type"]) {
            case 1: //巴利原文
                break;
            case 2: //逐词译
                $db_file = _DIR_PALICANON_WBW_ . "/p{$book}_wbw.db3";
                PDO_Connect("sqlite:{$db_file}");
                for ($iPar = $par_begin; $iPar <= $par_end; $iPar++) {
                    $table = "p{$book}_wbw_data";
                    $query = "SELECT * FROM \"{$table}\" WHERE (\"paragraph\" = " . $PDO->quote($iPar) . " ) and album_id={$album} ";
                    $Fetch = PDO_FetchAll($query);
                    $iFetch = count($Fetch);
                    if ($iFetch > 0) {
                        echo "<div id='par-wbw-b$book-$iPar' class='wbw_par'>";
                        for ($i = 0; $i < $iFetch; $i++) {
                            $wordtype = $Fetch[$i]["type"];
                            if ($wordtype != ".ctl.") {
                                echo "<div class='word'>";
                                echo "<div class='pali'>{$Fetch[$i]["word"]}</div>";
                                echo "<div class='mean'>{$Fetch[$i]["mean"]}</div>";
                                echo "<div class='case'>{$wordtype}#{$Fetch[$i]["gramma"]}</div>";
                                echo "</div>";
                            }
                        }
                        echo "</div>";
                        echo "<script>";
                        echo "document.getElementById('par-wbwdiv-b$book-$iPar').appendChild(document.getElementById('par-wbw-b$book-$iPar'));";
                        echo "</script>";

                    }
                }
                break;
            case 3:
                //译文
                $tocHtml = "";
                //打开翻译数据文件
                $db_file = _DIR_PALICANON_TRAN_ . "/p{$book}_translate.db3";
                PDO_Connect("sqlite:{$db_file}");
                $this_album_id = $album;
                $table = "p{$book}_translate_info";
                if ($par_begin == -1) {
                    //全文
                    $query = "SELECT * FROM '{$table}' WHERE album_id=$this_album_id ";
                } else {
                    //部分段落
                    $query = "SELECT * FROM '{$table}' WHERE (\"paragraph\" BETWEEN " . $PDO->quote($par_begin) . " AND " . $PDO->quote($par_end) . ") and album_id=$this_album_id ";
                }

                //查询翻译经文内容
                $FetchText = PDO_FetchAll($query);
                $iFetchText = count($FetchText);
                if ($iFetchText > 0) {
                    for ($i = 0; $i < $iFetchText; $i++) {
                        $currParNo = $FetchText[$i]["paragraph"];
                        //查另一个表，获取段落文本。一句一条记录。有些是一段一条记录
                        $table_data = "p{$book}_translate_data";
                        $query = "SELECT * FROM '{$table_data}' WHERE info_id={$FetchText[$i]["id"]}";
                        $aParaText = PDO_FetchAll($query);
                        $par_text = "";
                        foreach ($aParaText as $sent) {
                            $par_text .= $sent["text"];
                        }
                        //获取段落文本结束。
                        $par_text = str_replace("<pb></pb>", "<br/><pb></pb>", $par_text);
                        echo "<div id='par-translate-a$album-b$book-$currParNo' class='translate_text'>";
                        echo "<a name='par_$currParNo'></a>";
                        echo "<div id='text-a$album-b$book-$currParNo' class='text_level_" . $par_level["$currParNo"] . "' onclick='paragraph_click($album,$book,$currParNo)'>" . $par_text . "</div>";
                        echo "<div id='comm-a$album-b$book-$currParNo' class='comments'>";
                        echo "<div id='new-comm-a$album-b$book-$currParNo'  class='new_comments'></div>";
                        echo "</div>";
                        echo "</div>";
                        echo "<script>";
                        echo "add_new_res($album,$book,$currParNo,'$par_text');";
                        echo "document.getElementById('par-translate-b$book-$currParNo').appendChild(document.getElementById('par-translate-a$album-b$book-$currParNo'));";
                        echo "</script>";
                        //目录字符串
                        $tocLevel = $par_level["$currParNo"] + 1 - 1;
                        if ($tocLevel > 0 && $tocLevel < 8) {
                            $tocHtml .= "<div class='toc_item level_$tocLevel'><a href='#par_$currParNo'>{$par_text}</a></div>";
                        }
                    }
                    //设置标题栏的经文名称
                    echo "<script>";
                    echo "document.getElementById('tool_bar_title').innerHTML='" . $FetchText[0]["title"] . "'";
                    echo "</script>";
                }
                break;
            case 4:
                break;
            case 5:
                break;
            case 6:
                break;
            case 7:
                break;
        }
    }
    //添加注解
    PDO_Connect( _FILE_DB_COMMENTS_);

    if ($par_begin == -1) {
        $query = "SELECT * FROM \"comments\" WHERE album='$album'  order by id DESC";
    } else {
        $query = "SELECT * FROM \"comments\" WHERE  album='$album' AND (\"paragraph\" BETWEEN " . $PDO->quote($par_begin) . " AND " . $PDO->quote($par_end) . ") order by id DESC ";
    }
    //查询注解内容
    $FetchText = PDO_FetchAll($query);
    $iFetchText = count($FetchText);
    if ($iFetchText > 0) {
        for ($i = 0; $i < $iFetchText; $i++) {
            $currParNo = $FetchText[$i]["paragraph"];
            $comm_id = $FetchText[$i]["id"];

            echo "<div id='comm-id-" . $comm_id . "' class='comments_text_div'><div class='comments_text'>" . $FetchText[$i]["text"] . "</div><div><button>赞</button>" . $FetchText[$i]["reputable"] . "</div></div>";
            echo "<script>";
            echo "document.getElementById('comm-a$album-b$book-$currParNo').appendChild(document.getElementById('comm-id-" . $comm_id . "'));";
            echo "</script>";
        }

    }

    if ($par_next != -1) {
        echo "<a href='reader.php?book=$book&album=$album&paragraph=$par_next'>Next</a>";
    }

}
?>

	</div><!--main_text_view end-->

	<div id="new_comm_shell" style="display:none;">
		<div id="new_comm_div">
		<textarea id="new_comm_text"></textarea>
		<button onclick="new_comm_submit()">提交</button><button onclick="new_comm_cancel()">取消</button>
		</div>
	</div>

	<div id="dighest_edit_div" class="full_screen_window">
		<div class="win_caption">
		<div><button onclick="dighest_edit_cancle()">取消</button></div>
		<div><button onclick="dighest_edit_submit()">提交</button></div>
		</div>
		<div id="dighest_edit_body" class="win_body">
			<div>
				标题：<input id="dighest_edit_title" />
			</div>
			<div>
				简介：<textarea id="dighest_edit_summary"></textarea>
			</div>
			<div>
				标签：<input id="dighest_edit_taget" />
			</div>
			<div id="dighest_text_preview">
			</div>
		</div>
	</div>

		<!-- nav begin-->

		<div id="leftmenuinner" class="viewswitch_off">
			<div class="win_caption">
				<div><button onclick="setNaviVisibility()">返回</button></div>
				<div id="menubartoolbar_New">
					<ul class="common-tab">
						<li id="content_menu_li" class="common-tab_li_act" onclick="menuSelected_2(menu_toc,'content_menu_li')">目录</li>
						<li id="palicanon_menu_li" class="common-tab_li" onclick="menuSelected_2(menu_pali_cannon,'palicanon_menu_li')">批注</li>
						<li id="bookmark_menu_li" class="common-tab_li" onclick="menuSelected_2(menu_bookmark,'bookmark_menu_li')">书签</li>
					</ul>
				</div>
			</div>


			<div class='toc' id='leftmenuinnerinner'>
			<!-- toc begin -->
			<div class="menu" id="menu_toc">
				<a name="_Content" ></a>
				<select name="menu" onchange="show_toc_level(this)">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
				</select>
				<div class="tocitems" id="content"><?php echo $tocHtml; ?></div>
			</div>
			<!-- toc end -->
			<!-- comments begin -->
			<div class="menu" id="menu_comments">
				<div id="navi_comments_inner"></div>
			</div>
			<!-- comments end -->
			<!-- book mark begin -->
			<div class="menu" id="menu_bookmark" style="display:none;">
				<div>
					<input id="B_Bookmark_All" onclick="setBookmarkVisibility('all')" type="checkbox" style="width: 20px; height: 20px"  />
					<input id="B_Bookmark_A" onclick="setBookmarkVisibility('bma','B_Bookmark_A')" type="checkbox" style="width: 20px; height: 20px"  /><span class="bookmarkcolora , bookmarkcolorblock" >a</span>
					<input id="B_Bookmark_X" onclick="setBookmarkVisibility('bmx','B_Bookmark_X')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolorx , bookmarkcolorblock" >?</span>
					<input id="B_Bookmark_1" onclick="setBookmarkVisibility('bm1','B_Bookmark_1')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolor1 , bookmarkcolorblock" >1</span>
					<input id="B_Bookmark_2" onclick="setBookmarkVisibility('bm2','B_Bookmark_2')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolor2 , bookmarkcolorblock" >2</span>
					<input id="B_Bookmark_3" onclick="setBookmarkVisibility('bm3','B_Bookmark_3')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolor3 , bookmarkcolorblock" >3</span>
					<input id="B_Bookmark_4" onclick="setBookmarkVisibility('bm4','B_Bookmark_4')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolor4 , bookmarkcolorblock" >4</span>
					<input id="B_Bookmark_5" onclick="setBookmarkVisibility('bm5','B_Bookmark_5')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolor5 , bookmarkcolorblock" >5</span>
					<input id="B_Bookmark_0" onclick="setBookmarkVisibility('bm0','B_Bookmark_0')" type="checkbox" style="width: 20px; height: 20px" checked /><span class="bookmarkcolor0 , bookmarkcolorblock" >0</span>
					</div>
				<div id="navi_bookmark_inner"></div>
			</div>
			<!-- book mark end -->


			</div>

		</div>
		<!-- nav end -->
		<div id="BV" class="blackscreen" onclick="setNaviVisibility()"></div>
		<div id="mean_menu" ></div>
		<script>
		//lookup();
		$(".pali").mouseover(function(e){
			var targ
			if (!e) var e = window.event;
			if (e.target) targ = e.target;
			else if (e.srcElement) targ = e.srcElement;
			if (targ.nodeType == 3) // defeat Safari bug
			   targ = targ.parentNode;
			var pali_word;
			pali_word=targ.innerHTML;
			objCurrMouseOverPaliMean=targ.nextSibling;

			$("#tool_bar_title").html(pali_word);
			$("#mean_menu").html(getWordMeanMenu(pali_word));
			targ.parentNode.appendChild(document.getElementById("mean_menu"));

		});


		</script>

</body>
</html>