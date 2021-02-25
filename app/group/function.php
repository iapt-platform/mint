<?php

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

function group_get_name($id)
{
    if (isset($id)) {
        PDO_Connect("" . _FILE_DB_GROUP_);
        $query = "SELECT name FROM group_info  WHERE id=?";
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
        $dns = "" . _FILE_DB_GROUP_;
        $this->dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $buffer = array();
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
            $query = "SELECT name FROM group_info WHERE id= ? ";
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
}
