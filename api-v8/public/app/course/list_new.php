<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../ucenter/function.php';

global $PDO;
PDO_Connect("" . _FILE_DB_COURSE_);
$query = "SELECT * from course where 1  order by modify_time DESC limit 0,4";
$Fetch = PDO_FetchAll($query);

foreach ($Fetch as $row) {
    echo '<div class="card">';
    echo '<div>';
    /* 需協助完善封面代碼...
    echo '<img src="../../tmp/images/course/' . $iterator['id'] . '.jpg" alt="cover" width="120" height="120"  class="card_photo">';
*/
    echo '</div>';
    echo '<div class="course_right">';
    echo '<div class="title"><a href="../course/course.php?id=' . $row["id"] . '">' . $row["title"] . '</a></div>';
    echo '<div class="author">' . $_local->gui->speaker . '：';
    echo '<a href="../uhome/course.php?userid=' . $row['teacher'] . '">';
    echo ucenter_getA($row["teacher"]);
    echo '</a>';
    echo '</div>';
    echo '<div class="subtitle">' . $row["subtitle"] . '</div>';
    /*echo '<div class="summary">' . $row["summary"] . '</div>';*/
    echo '</div>';
    /*按讚數及觀看數（待定）
    echo '<div class="pd-10" style="display:flex;justify-content: space-between;">';
    echo '<button><svg t="1600445373282" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2368" width="32" height="32"><path fill="silver" d="M854.00064 412.66688h-275.99872v-35.99872c48-102.00064 35.99872-227.99872 0-288-12.00128-18.00192-35.99872-35.99872-54.00064-35.99872s-35.99872 6.00064-35.99872 54.00064c0 96-6.00064 137.99936-24.00256 179.99872-12.00128 29.99808-77.99808 96-156.00128 120.00256v480c12.00128 6.00064 35.99872 24.00256 54.00064 29.99808 18.00192 12.00128 48 18.00192 60.00128 18.00192h306.00192c77.99808 0 108.00128-29.99808 108.00128-66.00192 0-18.00192 0-29.99808-18.00192-35.99872V796.672c41.99936 0 83.99872-12.00128 83.99872-48 0-29.99808-12.00128-35.99872-18.00192-35.99872v-35.99872h6.00064c24.00256 0 60.00128-35.99872 60.00128-60.00128 0-18.00192-6.00064-35.99872-18.00192-41.99936-6.00064-6.00064-24.00256-6.00064-24.00256-6.00064v-35.99872s12.00128 0 24.00256-12.00128c18.00192-12.00128 18.00192-42.00448 18.00192-42.00448v-12.00128c0-29.99808-48-54.00064-96-54.00064zM67.99872 478.6688l35.99872 408.00256c6.00064 24.00256 24.00256 48 48 48h83.99872c6.00064 0 12.00128-6.00064 18.00192-12.00128s12.00128-6.00064 18.00192-12.00128V412.66688H128c-35.99872 0-60.00128 35.99872-60.00128 66.00192z" p-id="2369"></path></svg></button>';
    echo '<span id="num_like">3</span>';
    echo '<button><svg t="1600445467402" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4462" width="32" height="32"><path d="M604.37 488.16a93.87 93.87 0 1 1-93.92-93.88 94 94 0 0 1 93.92 93.88z" fill="silver" p-id="4463"></path><path d="M510.5 179c-246 0-445.39 227.26-445.39 318.12 0 113.65 199.43 318.17 445.39 318.17s445.39-204.52 445.39-318.17C955.89 406.26 756.46 179 510.5 179z m0 477.33c-92.68 0-168.07-75.44-168.07-168.17S417.77 320 510.45 320s168.17 75.42 168.17 168.13-75.44 168.2-168.17 168.2z" fill="silver" p-id="4464"></path></svg></button>';
    echo '<span id="num_subscribe">23</span>';
    echo '</div>';*/
    echo '</div>';
}
