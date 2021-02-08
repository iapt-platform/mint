<html>
<body>
<?php 
//显示log
require_once '../path.php';
require_once "../public/function.php";
require_once "../public/php/define.php";

if(isset($_COOKIE["uid"])){
	$active_type[10] = "_CHANNEL_EDIT_";
	$active_type[11] = "_CHANNEL_NEW_";
	$active_type[20] = "_ARTICLE_EDIT_";
	$active_type[21] = "_ARTICLE_NEW_";
	$active_type[30] = "_DICT_LOOKUP_";
	$active_type[40] = "_TERM_EDIT_";
	$active_type[41] = "_TERM_LOOKUP_";
	$active_type[60] = "_WBW_EDIT_";
	$active_type[70] = "_SENT_EDIT_";
	$active_type[71] = "_SENT_NEW_";
	$active_type[80] = "_COLLECTION_EDIT_";
	$active_type[81] = "_COLLECTION_NEW_";
	$active_type[90] = "_NISSAYA_FIND_";

	$dns = "sqlite:"._FILE_DB_USER_ACTIVE_LOG_;
	$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$query = "SELECT time,active,content,timezone  FROM log WHERE user_id = ? ";
		$stmt = $dbh->prepare($query);
		$stmt->execute(array($_COOKIE["uid"]));
		echo "<table>";
		
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			echo "<tr>";	
			foreach ($row as $key => $value) {
				# code...
				
				switch ($key) {
					case 'active':
						# code...
						$output =  isset($active_type[$value])? $active_type[$value] : $value;
						break;
					case 'time':
						# code...
						$output =  gmdate("Y-m-d H:i:s",($value+$row["timezone"])/1000);
						break;
					case 'timezone':
						# code...
						$output = round($value/1000/60/60,2) ;
						break;
					default:
						# code...
						$output =  $value;
						break;
				}
				echo "<td>$output</td>";
			}
			echo "</tr>";
		}
		
		echo "</table>";
}
else{
	echo "尚未登录";
}
?>
</body>
</html>