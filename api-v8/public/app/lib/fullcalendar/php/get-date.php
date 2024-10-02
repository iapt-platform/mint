<?php
//

require_once "../../../config.php";
require_once "../../../public/_pdo.php";

function get_teacher_course($teacher_id)
{

    $teacher = "  ";

    global $PDO;
    PDO_Connect("" . _FILE_DB_COURSE_);

    $query = "select * from course where teacher = ?  order by create_time DESC limit 0,100";

    $Fetch = PDO_FetchAll($query, array($teacher_id));

    $output = array();
    foreach ($Fetch as $key => $couse) {
        # code...
        $query = "select * from lesson where course_id = '{$couse["id"]}'   limit 0,300";
        $fAllLesson = PDO_FetchAll($query);
        foreach ($fAllLesson as $lesson) {
            $start = date("Y-m-d\TH:i:s+00:00", $lesson["date"] / 1000);
            $end = date("Y-m-d\TH:i:s+00:00", $lesson["date"] / 1000 + $lesson["duration"]);
            $output[] = array("id" => $lesson["id"],
                "title" => $couse["title"],
                "url" => "../../course/lesson.php?id=" . $lesson["id"],
                "start" => $start,
                "end" => $end,
            );
        }
    }
    return ($output);

}
