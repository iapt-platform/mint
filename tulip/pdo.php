<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/log.php';


class PdoHelper
{
    private $_pdo = null;

    public function connectDb()
    {
        /**
         * 连接数据库
         */
        $db = Config['database']['driver'];
        $db .= ":host=" . Config['database']['host'];
        $db .= ";port=" . Config['database']['port'];
        $db .= ";dbname=" . Config['database']['name'];
        $db .= ";user=" . Config['database']['user'];
        $db .= ";password=" . Config['database']['password'] . ";";

        myLog()->debug('connect to db host=' . Config['database']['host'] . ' name=' . Config['database']['name']);
        try {
            $PDO = new PDO(
                $db,
                Config['database']['user'],
                Config['database']['password'],
                array(PDO::ATTR_PERSISTENT => true)
            );
            myLog()->debug('connect to db success');
        } catch (PDOException $e) {
            myLog()->error('connect to db fail',['message'=>$e->getMessage()]);
            return false;
        }
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $PDO;
    }
    public function dbSelect($query, $params = null)
    {
        if ($this->_pdo === null) {
            return false;
        }
        if (isset($params)) {
            $stmt = $this->_pdo->prepare($query);
            $stmt->execute($params);
        } else {
            $stmt = $this->_pdo->query($query);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function execute($query, $params = null)
    {
        if (isset($params)) {
            $stmt = $this->_pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } else {
            return $this->_pdo->query($query);
        }
    }

    public function errorInfo()
    {
        return $this->_pdo->errorInfo();
    }
}
