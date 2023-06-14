<?php
require_once __DIR__ . '/casesuf.inc';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../config.php';

// Require Composer's autoloader.
require_once  __DIR__ . '/../../vendor/autoload.php';

$_book_index = null; //书的列表

/*
$mode:
0
 */
function getPaliWordBase($word, $mode = 0)
{
    global $case;
    //去除尾查
    $newWord = array();
    for ($row = 0; $row < count($case); $row++) {
        $len = mb_strlen($case[$row][1], "UTF-8");
        $end = mb_substr($word, 0 - $len, null, "UTF-8");
        if ($end == $case[$row][1]) {
            $base = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $case[$row][0];
            if ($base != $word) {
                $type = ".n.";
                $gramma = $case[$row][2];
                $parts = "{$base}+{$case[$row][1]}";
                if (isset($newWord[$base])) {
                    array_push($newWord[$base], array("type" => $type,
                        "gramma" => $gramma,
                        "parts" => $parts,
                    ));
                } else {
                    $newWord[$base] = array(array("type" => $type,
                        "gramma" => $gramma,
                        "parts" => $parts,
                    ));
                }
            }
        }
    }
    return ($newWord);
}

function _load_book_index()
{
    global $_book_index, $_dir_lang, $currLanguage, $_dir_book_index;
    if (file_exists($_dir_lang . $currLanguage . ".json")) {
        $_book_index = json_decode(file_get_contents($_dir_book_index . "a/" . $currLanguage . ".json"));
    } else {
        $_book_index = json_decode(file_get_contents($_dir_book_index . "a/default.json"));
    }
    //print_r($_book_index);
}

function _get_book_info($index)
{
    global $_book_index;
    foreach ($_book_index as $book) {
        if ($book->row == $index) {
            return ($book);
        }
    }
    return (null);
}

function _get_book_path($index)
{
    global $_book_index;
}

function _get_para_path($book, $paragraph)
{

    $dns = _FILE_DB_PALITEXT_;
    $dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $path = "";
    $parent = $paragraph;
    $deep = 0;
    $sFirstParentTitle = "";
    //循环查找父标题 得到整条路径
    while ($parent > -1) {
        $query = "SELECT * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ?";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array($book, $parent));
        $FetParent = $stmt->fetch(PDO::FETCH_ASSOC);
		if($FetParent){
			$toc = "<chapter book='{$book}' para='{$parent}' title='{$FetParent["toc"]}'>{$FetParent["toc"]}</chapter>";

			if ($path == "") {
				if ($FetParent["level"] < 100) {
					$path = $toc;
				} else {
					$path = "<para book='{$book}' para='{$parent}' title='{$FetParent["toc"]}'>{$paragraph}</para>";
				}
			} else {
				$path = $toc . $path;
			}
			if ($sFirstParentTitle == "") {
				$sFirstParentTitle = $FetParent["toc"];
			}
			$parent = $FetParent["parent"];
		}else{
			break;
		}

        $deep++;
        if ($deep > 5) {
            break;
        }
    }
    $dbh = null;
    return ($path);
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class USER
{
    public static function current(){
        if(isset($_COOKIE['token'])){
            $jwt = JWT::decode($_COOKIE['token'],new Key(APP_KEY,'HS512'));
            if($jwt->exp < time()){
                return [];
            }else{
                //有效的token
                return ['user_uid'=>$jwt->uid,'user_id'=>$jwt->id];
            }
        }else if(isset($_COOKIE['user_uid'])){
            return ['user_uid'=>$_COOKIE['user_uid'],'user_id'=>$_COOKIE['user_id']];
        }else{
            return [];
        }
    }
    public static function isSignin(){
        if(isset($_COOKIE['token'])){
            $jwt = JWT::decode($_COOKIE['token'],new Key(APP_KEY,'HS512'));
            if($jwt->exp < time()){
                return false;
            }else{
                //有效的token
                return true;
            }
        }else if(isset($_COOKIE['user_uid'])){
            return true;
        }else{
            return false;
        }
    }
}
class UUID
{
    public static function v3($namespace, $name)
    {
        if (!self::is_valid($namespace)) {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-', '{', '}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0; $i < strlen($nhex); $i += 2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        $hash = md5($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',

            // 32 bits for "time_low"
            substr($hash, 0, 8),

            // 16 bits for "time_mid"
            substr($hash, 8, 4),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 3
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }

    public static function v4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function v5($namespace, $name)
    {
        if (!self::is_valid($namespace)) {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-', '{', '}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0; $i < strlen($nhex); $i += 2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        $hash = sha1($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',

            // 32 bits for "time_low"
            substr($hash, 0, 8),

            // 16 bits for "time_mid"
            substr($hash, 8, 4),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 5
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }

    public static function is_valid($uuid)
    {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?' .
            '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }
}

function pali2english($subject)
{
    $subject = mb_strtolower($subject, 'UTF-8');
    $search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṇ', 'ṭ', 'ḍ', 'ḷ', 'ṃ');
    $replace = array('a', 'i', 'u', 'n', 'n', 'n', 't', 'd', 'l', 'm');
    $title_en = str_replace($search, $replace, $subject);
    return ($title_en);
}

function mTime()
{
    return (sprintf("%d", microtime(true) * 1000));
}

function getLanguageCode($inString)
{
    return ("zh-cn");
}
