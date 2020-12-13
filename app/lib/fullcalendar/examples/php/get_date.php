<?php
//

require_once "../../../../path.php";
require_once "../../../../public/_pdo.php";

if(isset($_GET["teacher"])){
    $teacher = " teacher = ? ";
}
else{
    $teacher = " 1= 1";
}

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);

$query = "select * from course where $teacher  order by create_time DESC limit 0,100";
if(isset($_GET["teacher"])){
	$Fetch = PDO_FetchAll($query,array($_GET["teacher"]));
}
else{
	$Fetch = PDO_FetchAll($query);
}

$output = array();
foreach ($Fetch as $key => $couse) {
	# code...
	$query = "select * from lesson where course_id = '{$couse["id"]}'   limit 0,300";
	$fAllLesson = PDO_FetchAll($query);
	foreach ($fAllLesson as  $lesson) {
		# code...
		#		"id": "999",
		#"title": "Dhammacakkha",
		#"url": "https://www.wikipali.org/app/course/lesson.php?id=6a42e993-8f7e-414a-a291-a4094764f992",
		#"start": "2020-12-11T08:00:00+06:30",
		#"end": "2020-12-11T09:30:00+06:30"
		$start = date("Y-m-d\TH:i:s+00:00",$lesson["date"]/1000);
		$end = date("Y-m-d\TH:i:s+00:00",$lesson["date"]/1000+$lesson["duration"]);
		$output[]=array("id"=>$lesson["id"],
									"title"=>$couse["title"],
									"url"=>"../../../course/lesson.php?id=".$lesson["id"],
									"start"=> $start,
									"end"=>$end
								);
	}
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>