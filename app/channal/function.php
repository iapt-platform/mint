<?php
require_once "../path.php";

function channel_get_title($id)
{
    if (isset($id)) {
		PDO_Connect( _FILE_DB_CHANNAL_);
		$query = "SELECT name FROM channal  WHERE id = ? ";
		$channel = PDO_FetchRow($query, array($id));
		if ($channel) {
			return $channel["name"];
		} else {
			return "";
		}
    } else {
        return "";
    }
}

class Channal
{
    public $dbh;
    public function __construct() {
        $dns = ""._FILE_DB_CHANNAL_;
        $this->dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
    }

    public function getChannal($id){
        $query = "SELECT * FROM channal WHERE id= ? ";
        $stmt = $this->dbh->prepare($query);
        $stmt->execute(array($id));
        $channal = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($channal)>0){
            return $channal[0];
        }
        else{
            return false;
        }
    }

}

?>