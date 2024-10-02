<?php
require_once '../config.php';
function get_setting()
{

    if (!isset($_COOKIE["userid"])) {
        $setting = json_decode(file_get_contents("../ucenter/default.json"), true);
    } else {
        $setting = json_decode(file_get_contents("../ucenter/default.json"), true);
        //打开数据库
        $dns = _FILE_DB_USERINFO_;
        $dbh = new PDO($dns, _DB_USERNAME_,_DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $query = "SELECT setting from "._TABLE_USER_INFO_." where userid = ? ";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($_COOKIE["userid"]));
        $fUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dbh = null;
        if (isset($fUser[0]["setting"])) {
            $my_setting = json_decode($fUser[0]["setting"], true);
            foreach ($setting as $key => $value) {
                if (mb_substr($key, 0, 1, "UTF-8") !== '_' && isset($my_setting[$key])) {
                    $setting[$key] = $my_setting[$key];
                }
            }
        }
    }
    return ($setting);
}

function inLangSetting($lang, $mySetting)
{
    # 通用语言 和 无译文语言 总是被采用
    if ($lang == "com" || $lang == "none") {
        return true;
    }
    # 用户没有设置语言
    if (count($mySetting) == 0) {
        return true;
    }
    foreach ($mySetting as $key => $value) {
        if (strpos($lang, "-") == false) {
            # 语族
            if ($lang === $value) {
                return true;
            }
        } else {
            $befor = strstr($lang, "-", true);
            if ($lang === $value || $befor === $value) {
                return true;
            }
        }
    }
    return false;
}
