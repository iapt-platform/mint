<?php

//--------------------------------------------------------------------------------------------------
// This script reads event data from a JSON file and outputs those events which are within the range
// supplied by the "start" and "end" GET parameters.
//
// An optional "timeZone" GET parameter will force all ISO8601 date stings to a given timeZone.
//
// Requires PHP 5.2.0 or higher.
//--------------------------------------------------------------------------------------------------

// Require our Event class and datetime utilities
require_once '../config.php';
require_once '../lib/fullcalendar/php/utils.php';

function covertTimeToString($time)
{
    $time = (int) $time;
    if ($time < 60) {
        return $time . "秒";
    } else if ($time < 3600) {
        return (floor($time / 60)) . "分钟";
    } else {
        $hour = floor($time / 3600);
        $min = floor(($time - ($hour * 3600)) / 60);
        return "{$hour}小时{$min}分钟";
    }
}

// Short-circuit if the client did not give us a date range.
if (!isset($_GET['start']) || !isset($_GET['end'])) {
    die("Please provide a date range.");
}

// Parse the start/end parameters.
// These are assumed to be ISO8601 strings with no time nor timeZone, like "2013-12-29".
// Since no timeZone will be present, they will parsed as UTC.
$range_start = parseDateTime($_GET['start']);
$range_end = parseDateTime($_GET['end']);

// Parse the timeZone parameter if it is present.
$time_zone = null;
if (isset($_GET['timeZone'])) {
    $time_zone = new DateTimeZone($_GET['timeZone']);
}

// Read and parse our events JSON file into an array of event data arrays.
$dns = _FILE_DB_USER_ACTIVE_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$query = "SELECT id , op_start, op_end , duration, hit  FROM "._TABLE_USER_OPERATION_FRAME_." WHERE user_id = ?";
$stmt = $dbh->prepare($query);
$stmt->execute(array($_COOKIE["user_id"]));
$allData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$input_arrays = array();
foreach ($allData as $key => $value) {
    # code...
    $strDuration = covertTimeToString($value["duration"] / 1000) . "-" . $value["hit"] . "次操作";
    $start = date("Y-m-d\TH:i:s+00:00", $value["op_start"] / 1000);
    $end = date("Y-m-d\TH:i:s+00:00", $value["op_end"] / 1000);
    $input_arrays[] = array("id" => $value["id"],
        "title" => $strDuration,
        "start" => $start,
        "end" => $end);
}

// Accumulate an output array of event data arrays.
$output_arrays = array();
foreach ($input_arrays as $array) {

    // Convert the input array into a useful Event object
    $event = new Event($array, $time_zone);

    // If the event is in-bounds, add it to the output
    if ($event->isWithinDayRange($range_start, $range_end)) {
        $output_arrays[] = $event->toArray();
    }
}

// Send JSON to the client.
echo json_encode($output_arrays);
