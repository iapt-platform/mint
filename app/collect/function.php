<?php
require_once '../path.php';

class CollectInfo
{
    private $dbh;
    private $buffer;
    public function __construct() {
        $dns = ""._FILE_DB_USER_ARTICLE_;
        $this->dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
        $buffer = array();
    }

    public function get($id){
        if(empty($id)){
            return array("title"=>"","id"=>"");
        }
        if(isset($buffer[$id])){
            return $buffer[$id];
        }
        if($this->dbh){
            $query = "SELECT id,title FROM collect WHERE id= ? limit 0,10";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute(array($id));
			$collect = $stmt->fetch(PDO::FETCH_ASSOC);
            if($collect){
                $buffer[$id] = $collect;
                return $buffer[$id];
            }
            else{
                $buffer[$id] =false;
                return $buffer[$id];
            }            
        }
        else{
            $buffer[$id] = false;
            return $buffer[$id];
        }
    }
}
?>