<?php
//查询group 列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../share/function.php';

$output = array();
if (isset($_GET["id"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    $id = $_GET["id"];
    $query = "SELECT * FROM "._TABLE_GROUP_INFO_."  WHERE uid = ? ";
    $Fetch = PDO_FetchRow($query, array($id));
    if ($Fetch) {
        $output["info"] = $Fetch;
        #列出组共享资源
		$output["file"] =share_res_list_get($id);

    }
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
