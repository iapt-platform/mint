<?php
//查询term字典
require_once "../path.php";
require_once "../public/_pdo.php";

//is login
if(isset($_COOKIE["username"]) && !empty($_COOKIE["username"])){
	$username = $_COOKIE["username"];
}
else{
	$username = "";
}

if(isset($_GET["op"])){
	$_op=$_GET["op"];
}
else if(isset($_POST["op"])){
	$_op=$_POST["op"];
}
if(isset($_GET["word"])){
	$_word=mb_strtolower($_GET["word"],'UTF-8');

}

if(isset($_GET["id"])){
	$_id=$_GET["id"];
}

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_PALI_SENTENCE_);

if(isset($_GET["sent"])){
	$_sent=mb_strtolower($_GET["sent"],'UTF-8');
}

// 输入一个句子，输出整个句子的单词 array
function words_of_sentence(string $sent) {
	$words = preg_split("/[ \.\[\]\{\}\-,';‘’–0123456789]+/", $sent); // 去除标点、数字
	$words = array_filter($words);  // 去除空词
	$words = array_filter($words, function($item) { 
		if ($item != 'ca' && $item != 'vā' && $item != 'na') return $item; }); // 去除 ca, vā 和 na
	return $words;
}

// 采用 jaccard 相似度，考虑到圣典中的相似句单词、句式都是非常接近的
function jaccard_similarity($words_of_sent1, $words_of_sent2) {
	$intersect_array = array_intersect($words_of_sent1, $words_of_sent2);
	$intersect = count($intersect_array);
	$union_array = array_merge($words_of_sent1, $words_of_sent2);
	$union = count($union_array) - $intersect;
	if ($intersect) {
		return $intersect / $union;
	} else {
		return 0;
	}
}

// 带顺序的 jaccard 算法，当前效果一般，TODO: 切片相同时加入得分
function ordered_jaccard_similarity($words_of_sent1, $words_of_sent2) {
	$score = 0;
	$k = min(count($words_of_sent1), count($words_of_sent2));
	for ($i=1; $i<$k; $i++) {
		$score += jaccard_similarity(
			array_slice($words_of_sent1, 0, $i),
			array_slice($words_of_sent2, 0, $i));
	}
	return $score / $k;
}

// 定义一个链表节点，方便 sim_sent_id 按照相似度插入
class sim_sent_node {
	public $id;
	public $jaccard_score;
	public $next;

	public function __construct($id = null, $jaccard_score = null, $next = null) {
		$this->id = $id;
		$this->jaccard_score = $jaccard_score;
		$this->next = $next;
	}
}

// 定义链表
class sim_sent_list {
	public $head;    // 头节点，默认一个虚头节点
	public $size;

	public function __construct() {
		$this->head = new sim_sent_node();
		$this->size = 0;
	}

	// 按照 jaccard_score 相似度插入
	public function jaccard_add($id, $jaccard_score) {
		$prev = $this->head;
		while ($prev->next != null && $prev->next->jaccard_score > $jaccard_score) {
			$prev = $prev->next;
		}
		if ($prev->next == null) {
			$prev->next = new sim_sent_node($id, $jaccard_score, null);
		} else {
			$insert_node = new sim_sent_node($id, $jaccard_score, $prev->next);
			$prev->next = $insert_node;
		}
		$this->size++;
	}

	// 调试用打印本链表
	public function print_list() {
		$prev = $this->head;
		if ($this->size == 0) {
			return;
		}
		while ($prev->next != null) {
			print($prev->next->id."\t".$prev->next->jaccard_score."\n");
			$prev = $prev->next;
		}
	}
}

// TODO: 将 current_id 的 similar_sent_list 存入数据库
function insert_similar_sent_list_into_sqlite($current_id, $list) {
	/* 使用这部分代码先为数据库添加一个 sim_sents 字段
	$add_column = "ALTER TABLE pali_sent ADD COLUMN sim_sents TEXT";
	$Action_add = PDO_Execute($add_column);
	$query = "PRAGMA TABLE_INFO (pali_sent)";
	$Fetch = PDO_FetchALL($query);
	print_r($Fetch);
	*/
	return;
}

// TODO: 考虑圣典中的相似句位置应该比较接近，可以减少比较量
function similar_sent_matrix() {
	$query = "select id,text from pali_sent limit 40000,1000";
	#$query = "select id,text from pali_sent where id = 10872 or id = 10716";
	$Fetch = PDO_FetchAll($query);

	foreach($Fetch as $current_row) {
		$current_id = $current_row['id'];
		$current_sent = $current_row['text'];
		$current_words = words_of_sentence($current_sent);

		if (count($current_words) > 5) { // 比较句子长度大于 5 的
			$current_sim_sent_list = new sim_sent_list(); // 新建相似句链表

			foreach($Fetch as $compare_row) {
				if ($current_row != $compare_row) {
					$compare_id = $compare_row['id'];
					$compare_sent = $compare_row['text'];
					$compare_words = words_of_sentence($compare_sent);

					if(count($compare_words) > 5) {
						$jaccard_score = jaccard_similarity($current_words, $compare_words);
						if ($jaccard_score > 0.3) {
							$current_sim_sent_list->jaccard_add($compare_id, $jaccard_score);
							#print($current_sim_sent_list->next->id);
						}
					}
				}
			} // end of foreach $compare_row

			if ($current_sim_sent_list->size != 0) {
				print("sim_sent_list of ".$current_id.":\n");
				$current_sim_sent_list->print_list();
				insert_similar_sent_list_into_sqlite($current_id, $current_sim_sent_list);
			}
		}
	} // end of foreach $current_row
}

similar_sent_matrix();

if (!isset($_op)) {
	exit(0);
}

switch($_op){
	case "get":
	{
		$Fetch=array();
		if(isset($_word)){
			$queryWord = str_replace(" ","",$_word);
			$query = "select book,paragraph,text from pali_sent where \"real\" like ".$PDO->quote("%".$queryWord.'%')." limit 0,5";
			$Fetch = PDO_FetchAll($query);
			$newList = array();
			//去掉重复的
			foreach($Fetch as $onerow){
				$found=false;
				foreach($newList as $new){
					if($onerow["text"]==$new["text"]){
						$found=true;
						break;
					}
				}
				if($found==false){
					array_push($newList,$onerow);
				}
			}
			$Fetch = $newList;

			if(count($Fetch)<5){
				$query = "select text from pali_sent where \"real_en\" like ".$PDO->quote('%'.$queryWord.'%')." limit 0,5";
				$Fetch2 = PDO_FetchAll($query);
				//去掉重复的
				foreach($Fetch2 as $onerow){
					$found=false;
					foreach($Fetch as $oldArray){
						if($onerow["word"]==$oldArray["word"]){
							$found=true;
							break;
						}
					}
					if($found==false){
						array_push($Fetch,$onerow);
					}
				}
			}
			
		}
		else if(isset($_id)){
		}
		echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
		break;
	}
}
?>