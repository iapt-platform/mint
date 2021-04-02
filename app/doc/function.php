<?php

require_once "../path.php";
require_once "../public/_pdo.php";

function pcs_get_title($id)
{
    if (isset($id)) {
		PDO_Connect("" . _FILE_DB_FILEINDEX_);
		$query = "SELECT title FROM fileindex  WHERE id = ? ";
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