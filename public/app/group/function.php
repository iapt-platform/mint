<?php

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

function group_get_name($id)
{
    if (isset($id)) {
        PDO_Connect(_FILE_DB_GROUP_);
        $query = "SELECT name FROM "._TABLE_GROUP_INFO_."  WHERE uid=?";
        $Fetch = PDO_FetchRow($query, array($id));
        if ($Fetch) {
            return $Fetch["name"];
        } else {
            return "";
        }
    } else {
        return "";
    }
}

class GroupInfo
{
    private $dbh;
    private $buffer;
    public function __construct()
    {
        $dns = _FILE_DB_GROUP_;
        $this->dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $buffer = array();
        $parentId = array();
    }

    public function getName($id)
    {
        if (empty($id)) {
            return "";
        }
        if (isset($buffer[$id])) {
            return $buffer[$id];
        }
        if ($this->dbh) {
            $query = "SELECT name FROM "._TABLE_GROUP_INFO_." WHERE uid= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $buffer[$id] = $user["name"];
                return $buffer[$id];
            } else {
                $buffer[$id] = "";
                return $buffer[$id];
            }
        } else {
            $buffer[$id] = "";
            return $buffer[$id];
        }
	}
	
	public function getParentId($id)
    {
        /*
        */
        return 0;

        if (empty($id)) {
            return "";
        }
        if (isset($parentId[$id])) {
            return $parentId[$id];
        }
        if ($this->dbh) {
            $query = "SELECT parent FROM "._TABLE_GROUP_INFO_." WHERE uid= ? ";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $parentId[$id] = $user["parent"];
                return $parentId[$id];
            } else {
                $parentId[$id] = "";
                return $parentId[$id];
            }
        } else {
            $parentId[$id] = "";
            return $parentId[$id];
        }
    }
}
