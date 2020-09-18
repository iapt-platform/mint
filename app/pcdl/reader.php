<?php
require_once "../public/_pdo.php";
require_once "../path.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';
require_once '../channal/function.php';
require_once '../ucenter/function.php';

global $_userinfo;
global $_channal;
$_userinfo = new UserInfo();
$_channal = new Channal();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="../studio/css/font.css"/>
	<link type="text/css" rel="stylesheet" href="css/reader.css"/>
	<link type="text/css" rel="stylesheet" href="css/reader_mob.css" media="screen and (max-width:767px)">
	<title id="page_title">PCD Reader</title>

	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>
	<script src="js/fixedsticky.js"></script>
	<script src="js/reader.js"></script>
	<script src="../public/js/comm.js"></script>
	<script src="../public/js/nty.js"></script>
	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
</head>
<body class="reader_body" >
<a name="page_head"></a>
	<script type="text/javascript"> 
	$(document).ready(function(){
		$(".toc_1_title").click(function(){
			$(this).parent().siblings().children(".toc_2").hide();
			$(this).siblings().slideToggle("200");
		});
	});
	</script>

<style>
	.edit_icon{
		display:inline-block;
		width:1.4em;
		height:1.4em;
	}
		#para_nav {
			display: flex;
			justify-content: space-between;
			padding: 5px 1em;
			border-top: 1px solid gray;
		}

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

.par_pali_div{
	margin-top:1em;
}
.par_pali_div{
	font-weight:700;
}
sent{
	font-weight:500;
	font-size:110%;
	line-height: 150%;
}
sent:hover{
	background-color:#fefec1;
}
para {
    color: #d27e17;
    background-color:  unset;
    min-width: 2em;
    display: inline-block;
    text-align: center;
    padding: 3px 5px;
    border-radius: 99px;
	margin-right: 5px;
	cursor:pointer;
	font-size:90%;
	font-weight:400;
}
para:hover{
    color: white;
    background-color:  #F1CA23;
}
paranum {
    font-weight: 700;
}
.sent_count{
	font-size:80%;
    color: white;
    background-color: #1cb70985;
    min-width: 2em;
    display: inline-block;
    text-align: center;
    padding: 2px 0;
    border-radius: 99px;
	margin-left: 5px;	
	cursor:pointer;
}
.toc_1{
	padding: 5px;
    cursor: pointer;
	border-left: 2px solid #aaaaaa;
}
.toc_1_title{
	font-weight:700;
}
.toc_2{
	font-weight:500;
	padding-left:1em;
	display:none;
}
.curr_chapter{
	border-color: #4d4dff;
	color: #4d4dff;
}
.toc_curr_chapter2{
	display:block;
}
.toc_title2 a{
	color:black;
	line-height:1.4em;
	text-decoration: none;
}
.toc_title2 a:hover{
	text-decoration: underline;
}
.curr_chapter_title2 a{
	color:#4d4dff;
	font-weight:900;
}
#leftmenuinner{
    width: 20em;
    max-width: 90%;
		overflow-y: scroll;
		border-right: unset;	
}
#leftmenuinnerinner{

}
#leftmenuinnerinner{
	margin-left: 2em;
    font-size: 0.8em;
	border-right: unset;
}
.sent_toc{
	font-weight:700;
	font-family: Noto serif;
    font-size: 150%;
}
note{
	color:blue;
}
.mine{
	color:green;
}
</style>
		<!-- tool bar begin-->
		<div id="main_tool_bar" class='reader_toolbar'>
			<div id="index_nav"> 
				<button onclick="setNaviVisibility()">
					<svg t='1598084571450' class='icon' viewBox='0 0 1029 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='6428' width='20px' height='20px'><path d='M159.744 69.632 53.248 69.632C28.672 69.632 4.096 90.112 4.096 118.784l0 94.208c0 28.672 24.576 49.152 53.248 49.152l102.4 0C184.32 266.24 208.896 241.664 208.896 212.992L208.896 118.784C208.896 90.112 184.32 69.632 159.744 69.632zM970.752 69.632 368.64 69.632c-28.672 0-57.344 24.576-57.344 49.152l0 94.208c0 28.672 32.768 49.152 57.344 49.152l598.016 0c28.672 0 57.344-24.576 57.344-49.152L1024 118.784C1028.096 94.208 999.424 69.632 970.752 69.632zM159.744 413.696 53.248 413.696c-28.672 0-53.248 24.576-53.248 49.152l0 94.208c0 28.672 24.576 49.152 53.248 49.152l102.4 0c28.672 0 53.248-24.576 53.248-49.152l0-94.208C208.896 438.272 184.32 413.696 159.744 413.696zM970.752 413.696 368.64 413.696c-28.672 0-57.344 24.576-57.344 49.152l0 94.208c0 28.672 32.768 49.152 57.344 49.152l598.016 0c28.672 0 57.344-24.576 57.344-49.152l0-94.208C1028.096 438.272 999.424 413.696 970.752 413.696zM159.744 757.76 53.248 757.76c-28.672 0-53.248 24.576-53.248 49.152l0 94.208c0 28.672 24.576 49.152 53.248 49.152l102.4 0c28.672 0 53.248-24.576 53.248-49.152l0-94.208C208.896 782.336 184.32 757.76 159.744 757.76zM970.752 761.856 368.64 761.856c-28.672 0-57.344 24.576-57.344 49.152l0 94.208c0 28.672 32.768 49.152 57.344 49.152l598.016 0c28.672 0 57.344-24.576 57.344-49.152l0-94.208C1028.096 782.336 999.424 761.856 970.752 761.856z' fill='#757AF7' p-id='6429'></path></svg>
				</button>
			</div>
			<div>
				<span id="tool_bar_title" style="font-family: 'Noto Serif';"><?php echo $_local->gui->title; ?></span>
			</div>
			<div style="display: flex;">
				<form action="../studio/project.php" method="post" onsubmit="return pali_canon_edit_now(this)" target="_blank" style="display: inline-block;">
					<div style="display:none;">
						<input type="input" name="op" value="create">
						<input type="hidden" name="view" value="<?php echo $_GET["view"]?>" />
						<input type="hidden" name="book" value="<?php echo $_GET["book"]?>" />
						<input type="hidden" id = "para" name="para" value="" />
						<input type="hidden" id = "para_end" name="para_end" value="" />
						<input type="hidden" id = "chapter_title" name="chapter_title" value="" />
							<textarea id="project_new_res_data" rows="3" cols="18" name="data"></textarea>
					</div>
					<input type="submit" value="<?php echo $_local->gui->edit_now; ?>">
				</form>
				<div class="case_dropdown">
					<p class="case_dropbtn"><button>
						<svg t='1598086376923' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='8426' width='20px' height='20px'><path d='M609.745455 453.818182v130.327273h69.818181V535.272727H744.727273v377.018182h95.418182V535.272727H907.636364v48.872728h69.818181V453.818182z' p-id='8427' fill='#757AF7'></path><path d='M677.236364 300.218182V111.709091H46.545455V300.218182h69.818181v-51.2h162.909091v663.272727h165.236364V249.018182h162.909091v51.2z' p-id='8428' fill='#757AF7'></path></svg>
					</button></p>
					<div class="case_dropdown-content" style="right: 0;width:10em;">
						<div ><button>A+</button><button>A-</button></div>
						<div ><button><?php echo $_local->gui->white;?></button><button><?php echo $_local->gui->dawn;//棕?></button><button><?php echo $_local->gui->night;//夜?></button></div>
					</div>
				</div>
				<div class="case_dropdown">
					<p class="case_dropbtn"><button>
					<svg t='1598086493824' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='9217' width='20px' height='20px'><path d='M912.695652 512m-111.304348 0a5 5 0 1 0 222.608696 0 5 5 0 1 0-222.608696 0Z' p-id='9218' fill='#757AF7'></path><path d='M512 512m-111.304348 0a5 5 0 1 0 222.608696 0 5 5 0 1 0-222.608696 0Z' p-id='9219' fill='#757AF7'></path><path d='M111.304348 512m-111.304348 0a5 5 0 1 0 222.608696 0 5 5 0 1 0-222.608696 0Z' p-id='9220' fill='#757AF7'></path></svg>
					</button></p>
					<div class="case_dropdown-content" style="right: 2em;min-width:6em;">
						<a onclick="tool_changed('dighest')"><?php echo $_local->gui->digest; //书摘?></a>
						<a onclick="tool_changed('comments')"><?php echo $_local->gui->comment; //批注?></a>
						<a onclick="tool_changed('target')"><?php echo $_local->gui->tag; //标签?></a>
						<a onclick="tool_changed('layout')"><?php echo $_local->gui->layout; //布局?></a>
						<a onclick="tool_changed('porpername')"><?php echo $_local->gui->dict_terms; //术语?></a>
						<a onclick="tool_changed('share')"><?php echo $_local->gui->share; //分享?></a>		
						<a onclick="tool_changed('fix')"><?php echo $_local->gui->modify; //修改?></a>								
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
$tocHtml="";

if(isset($_GET["album"])){
	$album=$_GET["album"];
}
if(isset($_GET["channal"])){
	$channal=$_GET["channal"];
}
if(isset($_GET["book"])){
	$book=$_GET["book"];
}
else{
	echo "no book id";
}
if(substr($book,0,1)=='p'){
	$book=substr($book,1);
}
if(isset($_GET["paragraph"])){
	$paragraph = $_GET["paragraph"];
}
else if(isset($_GET["para"])){
	$paragraph = $_GET["para"];
}
else{
	$paragraph = -1;
}

	if(isset($_GET["view"])){
		$_view = $_GET["view"];
	}
	else{
		echo "Error : 未定义必要的参数view";
		exit;
	}

	if(isset($_GET["display"])){
		$_display = $_GET["display"];
	}
	else{
		if($_view=="para" || $_view=="sent"){
			$_display = "sent";//默认值
		}
		else{
			$_display = "para";
		}
	}
	$tocList = array();
	if($_view=="chapter" || $_view=="para" || $_view=="sent" ){
		PDO_Connect("sqlite:"._FILE_DB_PALITEXT_);
		//生成目录
		$htmlToc2 = "<div><a href='#page_head'>页首</a></div>";
		//找到该位置对应的书
		$query = "select paragraph,level,chapter_len,parent from 'pali_text' where book='$book' and paragraph='$paragraph'";
		$FetchParInfo = PDO_FetchAll($query);
		$deep = 0;
		if(count($FetchParInfo)>0){
			$para = $FetchParInfo[0]["paragraph"];
			$level = $FetchParInfo[0]["level"];
			$chapter_len = $FetchParInfo[0]["chapter_len"];
			$parent = $FetchParInfo[0]["parent"];
			$currParaBegin = $para;
			$currParaEnd = $para + $chapter_len;
			$currParaLevel = $level;
			$currParaParentLevel = 0;
			//循环查找父标题 找到level=1的段落 也就是书名
			while($parent>-1){
				$query = "select paragraph,level,parent,chapter_len from pali_text where \"book\" = '{$book}' and \"paragraph\" = '{$parent}' limit 0,1";
				$FetParent = PDO_FetchAll($query);
				if(count($FetParent)>0){
					$para = $FetParent[0]["paragraph"];
					$level = $FetParent[0]["level"];
					$chapter_len = $FetParent[0]["chapter_len"];
					$parent = $FetParent[0]["parent"];
					if($currParaParentLevel==0){
						$currParaParentLevel = $level;
					}
				}
				$deep++;
				if($deep>8){
					break;
				}
			}

			$paraBegin = $para+1;
			$paraEnd = $para+$chapter_len;

			$query = "SELECT toc,paragraph,level,chapter_len,parent FROM 'pali_text' WHERE book='$book' AND (paragraph BETWEEN '$paraBegin' AND '$paraEnd') and level<100";
			$chapter_toc = PDO_FetchAll($query);
			$tocMaxLevel = 0;
			$tocMinLevel = 0;
			$tocBegin = 0;
			$tocEnd = 0;
			$toc1Level = 0;
			$toc2Level = 0;
			echo "<div><div>";
			foreach ($chapter_toc as $key => $value) {
				$tocList[$value["paragraph"]] = $value["level"];
				$classCurrToc="";
				$classCurrToc2 = "";
				$classCurrTocTitle2="";
				if($paragraph>=$value["paragraph"] && $paragraph<$value["paragraph"]+$value["chapter_len"]){
					$classCurrToc = " curr_chapter";
					$classCurrToc2 = " toc_curr_chapter2";
					$classCurrTocTitle2=" curr_chapter_title2";
				}
				
				if($tocBegin==0 || ($tocBegin>0 && $value["paragraph"]>=$tocEnd)){
					//开始新的标题1
					$tocBegin =$value["paragraph"];
					$tocEnd = $tocBegin + $value["chapter_len"];
					$toc1Level = $value["level"];
					if(isset($chapter_toc[$key+1])){
						if($chapter_toc[$key+1]["level"]>$toc1Level){
							$toc2Level = $chapter_toc[$key+1]["level"];
						}
						else{
							$tocBegin=0;
						}
					}
					$tocHtml .= "</div></div><div class='toc_1 {$classCurrToc}'>";
					$tocHtml .= "<div class='toc_1_title {$classCurrToc}'>{$value["toc"]}</div><div class='toc_2 $classCurrToc2'>";
				}
				else{
					//下一级标题
					if($value["level"]==$toc2Level  ){
						$tocHtml .= "<div class='toc_title2 {$classCurrToc}{$classCurrTocTitle2}'><a href='reader.php?view=chapter&book={$book}&para={$value["paragraph"]}&display={$_display}'>{$value["toc"]}</a></div>";
					}
				}

				//右侧目录
				
				if($value["paragraph"]>$currParaBegin && $value["paragraph"]<$currParaEnd){
					//$tocList[$value["paragraph"]] = $value["level"];
					$htmlToc2 .= "<div><a href='#para_{$value["paragraph"]}'>{$value["toc"]}</a></div>";
				}
				
			}
			echo "    </div></div>";
		}

		$htmlToc2 .= "<div><a href='#nav_foot'>导航</a></div>";
		$htmlToc2 .= "<div><a href='#sim_doc'>相关文档</a></div>";

		//获取段落信息 如 父段落 下一个段落等

		$query = "select * from 'pali_text' where book='$book' and paragraph='$paragraph'";
		$FetchParInfo = PDO_FetchAll($query);
		if(count($FetchParInfo)==0){
			echo "Error:no paragraph info";
			echo $query;
		}
		$currLevel = $FetchParInfo[0]["level"];
		$par_begin=$paragraph+1-1;
		if($_view=="para"){
			$par_end = $par_begin;
		}
		else{
			$par_end=$par_begin+$FetchParInfo[0]["chapter_len"]-1;	
		}
		
		$par_next=$FetchParInfo[0]["next_chapter"];	
		$par_prev=$FetchParInfo[0]["prev_chapter"];	
		$par_parent=$FetchParInfo[0]["parent"];	
		if($par_parent >= 0){
			$query = "select toc from 'pali_text' where book='$book' and paragraph='$par_parent'";
			$FetchToc = PDO_FetchAll($query);
			if(count($FetchToc)>0){
				$_parent_title = $FetchToc[0]["toc"];
			}
		}

		//查询标题
		if($_view=="chapter"){
			$par_title = $FetchParInfo[0]["toc"];
		}
		else{
			$par_title = $_parent_title;
		}
		//导航按钮		
		if($_view=="sent"){
			$next_para_link = "";
			$prev_para_link = "";
		}
		else{
			if($par_next != -1){
				$query = "select paragraph , toc from 'pali_text' where book='$book' and paragraph='$par_next' ";
				$FetchPara = PDO_FetchAll($query);
				if(count($FetchPara)>0){
					$next_para_link = "<a href='reader.php?view={$_view}&book={$book}&para={$par_next}&display={$_display}'><span id='para_nav_next'>{$FetchPara[0]["toc"]}</span><span  id='para_nav_next_a'>";
					$next_para_link .= "<svg t='1598093121925' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4451' width='32px' height='32px'><path d='M540.5696 102.4c-225.83296 0-409.6 183.74656-409.6 409.6s183.76704 409.6 409.6 409.6c225.85344 0 409.6-183.74656 409.6-409.6s-183.74656-409.6-409.6-409.6z m180.14208 439.84896l-109.19936 128.59392a46.65344 46.65344 0 0 1-65.86368 5.36576 46.71488 46.71488 0 0 1-5.38624-65.8432l43.86816-51.63008h-188.12928a46.6944 46.6944 0 1 1 0-93.42976h188.12928l-43.86816-51.63008a46.75584 46.75584 0 0 1 71.24992-60.47744l109.19936 128.59392c14.82752 17.408 14.82752 43.008 0 60.45696z' p-id='4452' fill='#757AF7'></path></svg>";
					$next_para_link .= "</span></a>";
				}
				else{
					$next_para_link = $_local->gui->text_without_title;
				}
			}
			else{
				$next_para_link = $_local->gui->end_of_text;
			}

			if($par_prev != -1){
				$query = "select paragraph , toc from 'pali_text' where book='$book' and paragraph='$par_prev' ";
				$FetchPara = PDO_FetchAll($query);
				if(count($FetchPara)>0){
					$prev_para_link = "<a href='reader.php?view={$_view}&book={$book}&para={$par_prev}&display={$_display}'><span id='para_nav_prev_a'>";
					$prev_para_link .= "<svg t='1598093521111' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4644' width='32' height='32'><path d='M540.5696 102.4c-225.83296 0-409.6 183.74656-409.6 409.6s183.76704 409.6 409.6 409.6c225.85344 0 409.6-183.74656 409.6-409.6s-183.74656-409.6-409.6-409.6z m144.54784 456.31488h-188.12928l43.84768 51.63008a46.6944 46.6944 0 0 1-35.59424 76.96384 46.55104 46.55104 0 0 1-35.61472-16.4864l-109.24032-128.59392a46.71488 46.71488 0 0 1 0-60.47744l109.24032-128.59392a46.6944 46.6944 0 1 1 71.20896 60.47744l-43.84768 51.63008h188.12928a46.6944 46.6944 0 1 1 0 93.45024z' p-id='4645' fill='#757AF7'></path></svg>";
					if($FetchPara[0]["toc"]==""){
						$prev_para_link .= "</span><span id='para_nav_prev'>（{$_local->gui->text_without_title}）</span></a>";

					}
					else{
						$prev_para_link .= "</span><span id='para_nav_prev'>{$FetchPara[0]["toc"]}</span></a>";
					}
				}
				else{
					$prev_para_link = $_local->gui->text_without_title;
				}
			}
			else{
				$prev_para_link = $_local->gui->begin_of_text;
			}			
		}

		//设置标题栏的经文名称
		echo "<script>";
		echo "document.getElementById('tool_bar_title').innerHTML='".$par_title."';\n";
		echo "$('#chapter_title').val('".$par_title."');\n";
		echo "$('#para_end').val('".$par_end."');\n";
		echo "$('#para').val('".$par_begin."');\n";
		echo "</script>";		
	}

	if($currParaLevel==1 || $currParaParentLevel==1){
		echo $_local->gui->chapter_select;
	}
	else{
	//上一级
	echo "<div>";
	switch($_view){
		case 1 :
		break;
		case 2:
		break;
		case 3:
		break;
		case 4:
		break;
		case 5:
		break;
		case 5:
		break;
		case 6:
		break;
		case "chapter":
			if($par_parent >= 0 && $currLevel>$tocMinLevel){
				echo "<a href='reader.php?view={$_view}&book={$book}&para={$par_parent}'>";
				echo "<svg t='1598083209786' class='icon' style='fill:#666666;' height='30px' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4926'><path d='M446.464 118.784l-254.976 256c-13.312 13.312-4.096 35.84 15.36 35.84H716.8c18.432 0 28.672-22.528 15.36-35.84l-254.976-256c-9.216-8.192-22.528-8.192-30.72 0zM563.2 796.672V533.504c0-11.264-9.216-21.504-21.504-21.504H379.904c-11.264 0-21.504 9.216-21.504 21.504v366.592c0 11.264 9.216 21.504 21.504 21.504h467.968c11.264 0 21.504-9.216 21.504-21.504V839.68c0-11.264-9.216-21.504-21.504-21.504H584.704c-12.288 0-21.504-9.216-21.504-21.504z m0 21.504' p-id='4927'></path></svg>";
				echo "{$_parent_title}</a>";
			}
		break;
		case "para":
			if($par_parent >= 0){
				echo "<a href='reader.php?view=chapter&book={$book}&para={$par_parent}'>";
				echo "<svg t='1598083209786' class='icon' style='fill:#666666;' height='30px' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4926'><path d='M446.464 118.784l-254.976 256c-13.312 13.312-4.096 35.84 15.36 35.84H716.8c18.432 0 28.672-22.528 15.36-35.84l-254.976-256c-9.216-8.192-22.528-8.192-30.72 0zM563.2 796.672V533.504c0-11.264-9.216-21.504-21.504-21.504H379.904c-11.264 0-21.504 9.216-21.504 21.504v366.592c0 11.264 9.216 21.504 21.504 21.504h467.968c11.264 0 21.504-9.216 21.504-21.504V839.68c0-11.264-9.216-21.504-21.504-21.504H584.704c-12.288 0-21.504-9.216-21.504-21.504z m0 21.504' p-id='4927'></path></svg>";
				echo "{$_parent_title}</a>";
			}
		break;
		case "sent":
				echo "<a href='reader.php?view=para&book={$book}&para={$paragraph}'>";
				echo "<svg t='1598083209786' class='icon' style='fill:#666666;' height='30px' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4926'><path d='M446.464 118.784l-254.976 256c-13.312 13.312-4.096 35.84 15.36 35.84H716.8c18.432 0 28.672-22.528 15.36-35.84l-254.976-256c-9.216-8.192-22.528-8.192-30.72 0zM563.2 796.672V533.504c0-11.264-9.216-21.504-21.504-21.504H379.904c-11.264 0-21.504 9.216-21.504 21.504v366.592c0 11.264 9.216 21.504 21.504 21.504h467.968c11.264 0 21.504-9.216 21.504-21.504V839.68c0-11.264-9.216-21.504-21.504-21.504H584.704c-12.288 0-21.504-9.216-21.504-21.504z m0 21.504' p-id='4927'></path></svg>";
				echo "{$paragraph}</a>";
		break;
		case 10:
		break;
	}
	echo "</div>";
	//生成一个段落空壳 等会儿查询数据，按照不同数据类型填充进去
	PDO_Connect("sqlite:"._FILE_DB_PALI_SENTENCE_);

	if($_display=="sent"){
		//逐句显示
		for($iPar=$par_begin;$iPar<=$par_end;$iPar++){
			if($_view=="sent"){
				$query = "select html, begin, end from 'pali_sent' where book='$book' and paragraph='$paragraph' and begin='{$_GET["begin"]}' and end ='{$_GET["end"]}'";
			}
			else{
				$query = "select html, begin, end from 'pali_sent' where book='$book' and paragraph='$iPar'";
			}
			if(isset($tocList[$iPar])){
				$sentClass = " sent_toc";
			}
			else{
				$sentClass = "";
			}
			$FetchSent = PDO_FetchAll($query);
			echo "<div id='par-b$book-$iPar' class='par_div'>";
			if($_view=="chapter"){
				echo "<para book='$book' para='$iPar' ";
				if(isset($tocList[$iPar])){
					echo " level = '{$tocList[$iPar]}' ";
				}
				echo ">$iPar</para><a name='para_{$iPar}'></a>";
			}
			foreach ($FetchSent as $key => $value) {
				echo "<div id='sent-pali-b$book-$iPar-{$value["begin"]}' class='par_pali_div'>";
				$pali_sent=$value["html"];
				echo "<sent  class='{$sentClass}' book='{$book}' para='{$iPar}' begin='{$value["begin"]}' end='{$value["end"]}' >".$pali_sent."</sent>";
				echo "</div>";
				echo "<div id='sent-wbwdiv-b$book-$iPar-{$value["begin"]}' class='par_translate_div'>";
				echo "</div>";
				echo "<div id='sent-translate-b$book-$iPar-{$value["begin"]}' class='par_translate_div'>";
				echo "</div>";
			}
			echo "</div>";
		}
	}
	else{
		//段落显示
		for($iPar=$par_begin;$iPar<=$par_end;$iPar++){
			if(isset($tocList[$iPar])){
				$sentClass = " sent_toc";
			}
			else{
				$sentClass = "";
			}
			$query = "select html , begin, end  from 'pali_sent' where book='$book' and paragraph='$iPar'";
			$FetchSent = PDO_FetchAll($query);
			echo "<div id='par-b$book-$iPar' class='par_div'>";
			echo "<div id='par-pali-b$book-$iPar' class='par_pali_div'>";
			
			if($_view=="chapter"){
				echo "<para book='$book' para='$iPar' ";
				if(isset($tocList[$iPar])){
					echo " level = '{$tocList[$iPar]}' ";
				}
				echo ">$iPar</para><a name='para_{$iPar}'></a>";
			}
			foreach ($FetchSent as $key => $value) {
				$pali_sent=$value["html"];
				echo "<sent class='{$sentClass}'  book='{$book}' para='{$iPar}' begin='{$value["begin"]}' end='{$value["end"]}' >{$pali_sent}</sent>";
			}
			echo "</div>";
			echo "<div id='par-wbwdiv-b$book-$iPar' class='par_translate_div'>";
			echo "</div>";
			echo "<div id='par-translate-b$book-$iPar' class='par_translate_div'>";
			echo "</div>";
			echo "<div id='par-note-b$book-$iPar' class='par_translate_div'>";
			echo "</div>";
			echo "</div>";
		}
	}	

	$strSimSent = "";
	if($_GET["view"]=="sent"){
		$query = "select sim_sents from 'pali_sent' where book='$book' and paragraph='$paragraph' and begin='{$_GET["begin"]}' and end ='{$_GET["end"]}'";
		$FetchSent = PDO_FetchOne($query);
		if(!empty($FetchSent)){
			$sim_sents = str_replace(",","','",$FetchSent);
			$sim_sents = "'".$sim_sents."'";
			$query = "SELECT book, paragraph,begin, end, text from 'pali_sent' where id IN ( {$sim_sents} ) ";
			$FetchSim = PDO_FetchAll($query);
			foreach ($FetchSim as $key => $value) {
				$strSimSent .= "<div>". $value["text"]."</div>";
				$strSimSent .= "<div><a href='../pcdl/reader.php?view=para&book={$value["book"]}&para={$value["paragraph"]}'>"._get_para_path($value["book"],$value["paragraph"])."</a></div><br/>";
			}
		}
	}

	if(isset($_GET["sent_mode"])){

	}

	//查询编辑者数量

		//查询句子译文内容
	PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);
	$dbh = new PDO("sqlite:"._FILE_DB_PALI_SENTENCE_, "", "");
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	//查询channal数量
	switch ($_view) {
		case 'chapter':
			$query = "SELECT channal from 'sentence' where book= ? and (paragraph between  ? and ? ) group by channal";
			$FetchChannal = PDO_FetchAll($query,array($book,$par_begin,$par_end));
		break;
		case 'para':
			# code...
			$query = "SELECT channal from 'sentence' where book= ? and paragraph = ? group by channal";
			$FetchChannal = PDO_FetchAll($query,array($book,$par_begin));
		break;
		case 'sent':
			# code...
			$query = "SELECT channal from 'sentence' where book= ? and paragraph =  ? AND begin =  ? AND end =  ? group by channal";
			$FetchChannal = PDO_FetchAll($query,array($book,$par_begin,$_GET["begin"],$_GET["end"]));
			break;	
	}
	
	

	for($iPar=$par_begin;$iPar<=$par_end;$iPar++){
		if($_view=="sent"){
			$FetchPaliSent = array(array("begin" => $_GET["begin"] , "end" => $_GET["end"]));
		}
		else{
			$query = "select begin, end from 'pali_sent' where book='$book' and paragraph='$iPar'";
			$stmt = $dbh->query($query);
			$FetchPaliSent = $stmt->fetchAll(PDO::FETCH_ASSOC);			
		}

		//使用巴利语句子列表 查询译文
		foreach ($FetchPaliSent as $key => $value) {
			$begin = $value["begin"];
			$end = $value["end"];
			if(isset($_GET["channal"])){
				$query_channal = " AND channal=".$PDO->quote($_GET["channal"]);
			}
			$sent_count = 1;
			if($_view=="sent"){
				//显示一个巴利句子 以及相应的译文
				if(isset($_GET["sent"])){
					//如果指定译文句子编号，只显示该句子（和后面的跟帖）
					$query="SELECT * FROM \"sentence\" WHERE id = ".$PDO->quote($_GET["sent"]);
				}
				else{
					$query="SELECT * FROM \"sentence\" WHERE (book = ".$PDO->quote($book)." AND  \"paragraph\" = ".$PDO->quote($iPar)." AND begin = '$begin' AND end = '$end'  AND strlen <> 0 AND parent = ''  ) {$query_channal}  order by modify_time  DESC";
				}
				
			}
			else{
				$query = "SELECT * FROM \"sentence\" WHERE book = ".$PDO->quote($book)." AND  \"paragraph\" = ".$PDO->quote($iPar)." AND begin = '$begin' AND end = '$end' AND strlen <> 0  {$query_channal} order by modify_time DESC  limit 0, 1";
				$query_count = "SELECT count(book) FROM \"sentence\" WHERE book = ".$PDO->quote($book)." AND  \"paragraph\" = ".$PDO->quote($iPar)." AND begin = '$begin' AND end = '$end' AND strlen > 0  {$query_channal} ";
				$sent_count = PDO_FetchOne($query_count);
				if($sent_count>9){
					$sent_count = "9+";
				}
			}

			$FetchText = PDO_FetchAll($query);
			$i = 0;
			foreach ($FetchText as $key => $value) {
				$thisSent = $value;
				$sentClass = "";
				# 找出句子中 我贡献的，优先显示
				if($_view != "sent"){
					if(isset($_COOKIE["userid"])){
						if($thisSent["editor"] !== $_COOKIE["userid"]){
							$query="SELECT * FROM sentence WHERE parent = ".$PDO->quote($thisSent["id"])." AND editor = ".$PDO->quote($_COOKIE["userid"])." order by modify_time DESC limit 0,1";
							$myText = PDO_FetchAll($query);
							if(count($myText)>0){
								$thisSent = $myText[0];
								$sentClass = "mine";
							}
						}
					}
				}
				echo render_sent($thisSent,$i,$_display,$sent_count,$sentClass);
				$i++;
			}
			if(count($FetchText)>0){
				if(isset($_GET["sent"])){
					//如果指定句子译文编号，显示句子的跟帖
					$query="SELECT * FROM \"sentence\" WHERE parent = ".$PDO->quote($_GET["sent"]);
					$FetchChildren = PDO_FetchAll($query);
					$i = 0;
					echo "<div style='margin-left:1em;'>";
					foreach ($FetchChildren as $key => $value) {
						echo render_sent($value,$i,$_display,$sent_count);
						$i++;
					}					
					echo "</div>";
				}
			}
		}
	}
		//查询句子译文内容结束

	echo "<div id='para_nav'><a name='nav_foot'></a>";
	echo "<div style='display:inline-flex;'>";
	echo "<svg t='1598094361320' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4933' width='32' height='32'><path d='M698.75712 684.4416a81.92 81.92 0 0 1-124.88704 106.06592l-191.488-225.4848a81.89952 81.89952 0 0 1 0-106.06592l191.488-225.4848a81.92 81.92 0 0 1 124.88704 106.06592l-146.45248 172.46208 146.45248 172.4416z' p-id='4934' fill='#757AF7'></path></svg>";
	echo "$prev_para_link</div>";
	echo "<div style='display:inline-flex;'>$next_para_link";
	echo "<svg t='1598094021808' class='icon' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='4451' width='32' height='32'><path d='M698.75712 565.02272l-191.488 225.4848a81.73568 81.73568 0 0 1-62.48448 28.89728 81.89952 81.89952 0 0 1-62.40256-134.94272l146.432-172.4416-146.432-172.4416a81.92 81.92 0 0 1 124.88704-106.06592l191.488 225.4848a81.87904 81.87904 0 0 1 0 106.02496z' p-id='4452' fill='#757AF7'></path></svg>";
	echo "</div>";

	echo "</div>";
		
	}
?>

<?php
function render_sent($sent_data,$sn,$display_mode,$sent_count,$class=""){
	global $_userinfo;
	global $_channal;
	global $PDO;
	$output = "";
		$currParNo=$sent_data["paragraph"];
		$book = $sent_data["book"];
		if($display_mode=="sent"){
			$sent_style = "display:block";
		}
		else{
			$sent_style = "";
		}
		if(!empty($sent_data["parent"])){
			$reply_style = " sent_reply ";
		}
		else{
			$reply_style = "";
		}
		$tran_text = str_replace("[[","<term status='0'>",$sent_data["text"]);
		$tran_text = str_replace("]]","</term>",$tran_text);
		$output .= "<sent_trans style='{$sent_style}{$reply_style}' id='sent-tran-b{$book}-{$currParNo}-{$sent_data["begin"]}-{$sn}' class='sent_trans ' book='$book' para='$currParNo' begin='{$sent_data["begin"]}'>";
		
		$output .= "<span class='sent_text {$class}' ";
		$output .= " sent_id='".$sent_data["id"]."'";
		$output .= " editor='".$sent_data["editor"]."'";
		$output .= " book='".$sent_data["book"]."'";
		$output .= " para='".$sent_data["paragraph"]."'";
		$output .= " begin='".$sent_data["begin"]."'";
		$output .= " end='".$sent_data["end"]."'";
		$output .= " lang='".$sent_data["language"]."'";
		$output .= " channal='".$sent_data["channal"]."'";
		$output .= " tag='".$sent_data["tag"]."'";
		$output .= " author='".$sent_data["author"]."'";
		$output .= " text='".$sent_data["text"]."'";
		$output .= ">";
		$output .= $tran_text;
		$output .= "</span>";
		if($display_mode=="sent"){
			if((isset($_GET["channal"]) || $_GET["view"]=="sent")  ){
				if($sent_data["editor"] == $_COOKIE["userid"]){
					$output .= "<svg class='edit_icon';'><use xlink:href='../studio/svg/icon.svg#ic_mode_edit'></use></svg>";
				}
				
			}
			else{
				$output .= "<span class='sent_count'>$sent_count</span>";
			}
		}
		if($_GET["view"]=="sent"){
			$name = $_userinfo->getName($sent_data["editor"]);
			$channalInfo = $_channal->getChannal($sent_data["channal"]);

			$query="SELECT count(*) FROM \"sentence\" WHERE parent = ".$PDO->quote($sent_data["id"]);
			$edit_count = PDO_FetchOne($query);
		
			$output .= "<div style='font-size:80%;color:gray;margin-bottom:1em;'>Author：$name / {$channalInfo["name"]} 赞：( ) ";
			if($sent_data["editor"] == $_COOKIE["userid"]){
				$output .= "<span>润校</span>：";
			}
			else{
				$output .= "<edit>润校</edit>：";
			}
			
			$output .= "({$edit_count})  ";
			if($sent_data["editor"] != $_COOKIE["userid"]){
				$output .= "<span onclick='sent_apply()'>采纳</span>";
			}
			$output .="</div>";
		}


		$output .= "</sent_trans>";

		
		if($_GET["view"]!="sent"){
			$output .= "<script>";
			if($display_mode=="sent"){
				$output .= "document.getElementById('sent-translate-b{$book}-{$currParNo}-{$sent_data["begin"]}').appendChild(document.getElementById('sent-tran-b{$book}-{$currParNo}-{$sent_data["begin"]}-{$sn}'));";
			}
			else{
				$output .= "document.getElementById('par-translate-b{$book}-{$currParNo}').appendChild(document.getElementById('sent-tran-b{$book}-{$currParNo}-{$sent_data["begin"]}-{$sn}'));";
			}
			$output .= "</script>";			
		}

		return $output;
	}
	
?>

<div>
<a name="sim_doc"></a>
<div>相似句子</div>
<?php echo $strSimSent; ?>
<div>相关段落</div>
<ul>
<?php
//查找相关标题
	if(strtolower(mb_substr($par_title,mb_strlen($par_title,"UTF-8")-7,null,"UTF-8"))=="vaṇṇanā"){
		$searchToc = strtolower(mb_substr($par_title,0,-7,"UTF-8"));
	}
	else{
		$searchToc = strtolower(mb_substr($par_title,0,-1,"UTF-8"));
	}
	
	PDO_Connect("sqlite:"._FILE_DB_RESRES_INDEX_);
	$query = "select book, paragraph,title from 'index' where  \"title\" like ".$PDO->quote($searchToc.'%')."  limit 0,50";
	$Fetch = PDO_FetchAll($query);
	foreach ($Fetch as $key => $value) {
		echo "<div style='margin-bottom: 0.5em;'>";
		echo "<div><a href='reader.php?view=chapter&book={$value["book"]}&para={$value["paragraph"]}' target='_blank'> {$value["title"]} </a></div>";
		echo "<div>". _get_para_path($value["book"],$value["paragraph"])."</div>";
		echo "</div>";
	}
?>
</ul>
</div>

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

	<div id="right_panal_toc" style="position: fixed;top:3em;width:17em;left: calc(100% - 17em);height:auto; min-height:30em;border-left: 1px solid gray;    font-size: 80%;padding: 2em 0.5em;">
	<?php  
	if($currLevel>=$tocMinLevel){
		echo $htmlToc2; 
	}

	echo "<div>Channal</div>";

	
	echo "<div>";
	echo "<a href='../pcdl/reader.php?view={$_GET["view"]}";
	echo "&book=".$_GET["book"];
	echo "&para=".$_GET["para"];
	if(isset($_GET["begin"])){
		echo "&begin=".$_GET["begin"];
	}
	if(isset($_GET["end"])){
		echo "&end=".$_GET["end"];
	}
	echo "' >";
	echo "全部";
	echo "</a>";
	echo "</div>";
	foreach ($FetchChannal as $key => $value) {
		# code...
		echo "<div>";
		echo "<a href='../pcdl/reader.php?view={$_GET["view"]}";
		echo "&book=".$_GET["book"];
		echo "&para=".$_GET["para"];
		if(isset($_GET["begin"])){
			echo "&begin=".$_GET["begin"];
		}
		if(isset($_GET["end"])){
			echo "&end=".$_GET["end"];
		}
		if(isset($_GET["display"])){
			echo "&display=".$_GET["display"];
		}
		echo "&channal=".$value["channal"]."' >";
		$channalInfo = $_channal->getChannal($value["channal"]);
		if($channalInfo){
			echo $_userinfo->getName($channalInfo["owner"]);
			echo '/'.$channalInfo["name"];			
		}
		echo "</a>";
		echo "</div>";
	}
	?>

	</div>

	</div>
	<!-- 全屏 黑色背景 -->	
	<div id="BV" class="blackscreen" onclick="setNaviVisibility()"></div>
		<!-- nav begin--> 

	<div id="leftmenuinner" class="viewswitch_off">
			<div class="win_caption">
				<div><button id="left_menu_hide" onclick="setNaviVisibility()">返回</button></div>
				<div id="menubartoolbar_New">
					<ul class="common-tab">
						<li id="content_menu_li" class="common-tab_li_act" onclick="menuSelected_2(menu_toc,'content_menu_li')">目录</li>
						<li id="palicanon_menu_li" class="common-tab_li" onclick="menuSelected_2(menu_pali_cannon,'palicanon_menu_li')">批注</li>
						<li id="bookmark_menu_li" class="common-tab_li" onclick="menuSelected_2(menu_bookmark,'bookmark_menu_li')">书签</li>
					</ul>
				</div>
			</div>
			
		<div class='toc' id='leftmenuinnerinner' style="font-family: 'Noto Serif';">	
			<!-- toc begin -->
			<div class="menu" id="menu_toc">
				<a name="_Content" ></a>
				<div  id="toc_content"><?php echo $tocHtml; ?></div>
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
	</div>

	<div id="mean_menu" ></div>

	<div id="sent_modify_win">
		<div>
			<textarea id="sent_modify_text" style="display:block;width:100%;">
			</textarea>
			<button onclick="trans_sent_save()">Save</button>
			<button onclick="trans_sent_cancel()">Cancel</button>
		</div>
	</div>



<script>


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

		$("para").mouseenter(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			$("sent[book='"+book+"'][para='"+para+"']").css("background-color","#fefec1");
		});
		$("para").mouseleave(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			$("sent[book='"+book+"'][para='"+para+"']").css("background-color","unset");
		});

		$("sent").click(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let begin = $(this).attr("begin");
			let end = $(this).attr("end");
			window.location.assign("reader.php?view=sent&book="+book+"&para="+para+"&begin="+begin+"&end="+end);
		});



		$("sent").mouseenter(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let begin = $(this).attr("begin");
			$(this).css("background-color","#fefec1");
			$("sent_trans[book='"+book+"'][para='"+para+"'][begin='"+begin+"']").css("background-color","#fefec1");
		});
		$("sent").mouseleave(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let begin = $(this).attr("begin");
			$(this).css("background-color","unset");
			$("sent_trans[book='"+book+"'][para='"+para+"'][begin='"+begin+"']").css("background-color","unset");
		});

		$("sent_trans").mouseenter(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let begin = $(this).attr("begin");
			$(this).css("background-color","#fefec1");
			$("sent[book='"+book+"'][para='"+para+"'][begin='"+begin+"']").css("background-color","#fefec1");
		});
		$("sent_trans").mouseleave(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let begin = $(this).attr("begin");
			$(this).css("background-color","unset");
			$("sent[book='"+book+"'][para='"+para+"'][begin='"+begin+"']").css("background-color","unset");
		});



		$(".sent_text").click(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let begin = $(this).attr("begin");
			let end = $(this).attr("end");
			let id = $(this).attr("sent_id");
			window.location.assign("reader.php?view=sent&book="+book+"&para="+para+"&begin="+begin+"&end="+end+"&sent="+id);
		});


		$(".edit_icon").click(function(e){
			let objSent = $(this).parent().children(".sent_text").first();
			if(objSent){
				let text = objSent.attr("text");
				$("#sent_modify_text").val(text);
				$("#sent_modify_text").attr("sent_id",objSent.attr("sent_id"));
				$("#sent_modify_text").attr("editor",objSent.attr("editor"));
				$("#sent_modify_text").attr("book",objSent.attr("book"));
				$("#sent_modify_text").attr("para",objSent.attr("para"));
				$("#sent_modify_text").attr("begin",objSent.attr("begin"));
				$("#sent_modify_text").attr("end",objSent.attr("end"));
				$("#sent_modify_text").attr("lang",objSent.attr("lang"));
				$("#sent_modify_text").attr("tag",objSent.attr("tag"));
				
				$(this).parent().append($("#sent_modify_win"));			
				$("#sent_modify_win").show();	
			}
		});
	
		$("edit").click(function(e){
			let objSent = $(this).parent().parent().children(".sent_text").first();
			if(objSent){
				let text = objSent.attr("text");
				$("#sent_modify_text").val(text);
				$("#sent_modify_text").attr("sent_id",objSent.attr("sent_id"));
				$("#sent_modify_text").attr("editor",objSent.attr("editor"));
				$("#sent_modify_text").attr("book",objSent.attr("book"));
				$("#sent_modify_text").attr("para",objSent.attr("para"));
				$("#sent_modify_text").attr("begin",objSent.attr("begin"));
				$("#sent_modify_text").attr("end",objSent.attr("end"));
				$("#sent_modify_text").attr("lang",objSent.attr("lang"));
				$("#sent_modify_text").attr("tag",objSent.attr("tag"));
				
				$(this).parent().append($("#sent_modify_win"));		
				$("#sent_modify_win").show();		
			}
		});

		$("para").click(function(e){
			let book = $(this).attr("book");
			let para = $(this).attr("para");
			let level = $(this).attr("level");
			let view = "para";
			if(level && level<100){
				view = "chapter";
			}
			window.location.assign("reader.php?view="+view+"&book="+book+"&para="+para);
		});

		term_updata_translation();
		var wordlist =  new Array();
	$("term").each(function(index,element){
		wordlist.push($(this).attr("pali"));
	}
	);
	
	function haha(){
		var wordquery ="('" + wordlist.join("','")+"')";
		$.post("../term/term.php",
		{
			op:"extract",
			words:wordquery
		},
		function(data,status){
			if(data.length>0){
				try{
					arrMyTerm = JSON.parse(data);
					term_updata_translation();
				}
				catch(e){
					console.error(e.error+" data:"+data);
				}
			}
		});			
	}

</script>
	
</body>
</html>