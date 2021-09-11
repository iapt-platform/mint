<?php
require_once "../path.php";
//error handler function
function customError($errno, $errstr)
{
    echo "<b>Error:</b> [$errno] $errstr";
}

//set error handler
set_error_handler("customError");

function user_db_is_exist()
{
    $PDO = new PDO("" . _FILE_DB_USERINFO_, "", "");
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $stmt = $PDO->query("SELECT count(*) as co FROM sqlite_master WHERE type=\"table\" AND name = \"user\"");

    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        if ($row[0] > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

if (user_db_is_exist()) {
    if (!isset($_COOKIE["user_uid"])) {
        echo "请登陆后执行此操作";
        exit;
    }
}
