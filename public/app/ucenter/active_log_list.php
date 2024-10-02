<html>
<body>
<?php
//显示log
require_once '../config.php';
require_once "../public/function.php";
require_once "../public/php/define.php";

if (isset($_COOKIE["user_id"])) {

    $active_type[10] = "_CHANNEL_EDIT_";//编辑channel信息——项目
    $active_type[11] = "_CHANNEL_NEW_";//创建channel——项目
    $active_type[20] = "_ARTICLE_EDIT_";//article编辑——项目
    $active_type[21] = "_ARTICLE_NEW_";//article创建——项目
    $active_type[30] = "_DICT_LOOKUP_";//查字典——通用
    $active_type[40] = "_TERM_EDIT_";//编辑术语——研究
    $active_type[41] = "_TERM_LOOKUP_";//术语查询——研究
    $active_type[60] = "_WBW_EDIT_";//逐词解析编辑——基本功
    $active_type[70] = "_SENT_EDIT_";//句子译文编辑——翻译
    $active_type[71] = "_SENT_NEW_";//新建句子译文——翻译
    $active_type[80] = "_COLLECTION_EDIT_";//文集编辑——项目
    $active_type[81] = "_COLLECTION_NEW_";//文集编辑——项目
    $active_type[90] = "_NISSAYA_FIND_";//找nissaya——研究

    $dns = _FILE_DB_USER_ACTIVE_LOG_;
    $dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "SELECT create_time,op_type_id,content,timezone  FROM "._TABLE_USER_OPERATION_LOG_." WHERE user_id = ? ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_COOKIE["user_id"]));
    echo "<table>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            # code...

            switch ($key) {
                case 'active':
                    # code...
                    $output = isset($active_type[$value]) ? $active_type[$value] : $value;
                    break;
                case 'time':
                    # code...
                    $output = gmdate("Y-m-d H:i:s", ($value + $row["timezone"]) / 1000);
                    break;
                case 'timezone':
                    # code...
                    $output = round($value / 1000 / 60 / 60, 2);
                    break;
                default:
                    # code...
                    $output = $value;
                    break;
            }
            echo "<td>$output</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "尚未登录";
}
?>
</body>
</html>