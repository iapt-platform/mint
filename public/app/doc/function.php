<?php

require_once "../config.php";
require_once "../public/_pdo.php";

function pcs_get_title($id)
{
    if (isset($id)) {
		PDO_Connect(_FILE_DB_FILEINDEX_);
		$query = "SELECT title FROM "._TABLE_FILEINDEX_."  WHERE uid = ? ";
		$file = PDO_FetchRow($query, array($id));
		if ($file) {
			return $file["title"];
		} else {
			return "";
		}
    } else {
        return "";
    }
}