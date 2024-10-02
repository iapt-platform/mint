<?php
include "../public/config.php";
include "../public/_pdo.php";

		if(isset($_GET['id'])){
			$word_id=$_GET['id'];
		}
		else{
			echo "没有参数 id";
			exit;
		}
						
		PDO_Connect(_FILE_DB_REF_);
		$query = "SELECT * from dict where id = ? ";
		$Fetch = PDO_FetchAll($query,array($word_id));
		
		#Todo 可疑代码带修补
		$dictFileName=$dir_dict_3rd."all.db3";
		PDO_Connect("$dictFileName");
		
		foreach($Fetch as $word){
			echo "<h3>".$word["paliword"]."</h3>";
			if($word["status"]>1){
				$query = "SELECT * from dict where from = ? ";
				$FetchRichWord = PDO_FetchAll($query,array($word["id"]));
				echo "<div id='final_word'>";
				echo "<div id='final_word_header'>已编辑数据<button onclick='final_word_show_hide()'>显示/隐藏</bnutton></div>";
				echo "<div id='final_word_body'>";
				echo "<table>";
				foreach($FetchRichWord as $richword){
					echo "<tr>";
					echo "<td>".$richword["pali"]."</td>";
					echo "<td>".$richword["type"]."</td>";
					echo "<td>".$richword["gramma"]."</td>";
					echo "<td>".$richword["mean"]."</td>";
					echo "<td>".$richword["note"]."</td>";
					echo "<td>".$richword["factors"]."</td>";
					echo "<td>".$richword["factormean"]."</td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "</div>";
				echo "</div>";
				echo "<div style=\"margin-top: 1em;\">新的编辑</div>";
			}
			echo "<div id='word_org_text'>".$word["mean"]."</div>";
		}

?>