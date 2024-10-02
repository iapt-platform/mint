<?php
function PDO_Connect($dsn, $user="", $password="")
{
    global $PDO;
    $PDO = new PDO($dsn,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
function PDO_FetchOne($query, $params=null)
{
    global $PDO;
    if (isset($params)) {
        $stmt = $PDO->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $PDO->query($query);
    }
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        return $row[0];
    } else {
        return false;
    }
}

function PDO_FetchNum($query, $params=null)
{
    global $PDO;
    if (isset($params)) {
        $stmt = $PDO->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $PDO->query($query);
    }
    return PDO::FETCH_NUM;

}
function PDO_FetchRow($query, $params=null)
{
    global $PDO;
    if (isset($params)) {
        $stmt = $PDO->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $PDO->query($query);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function PDO_FetchAll($query, $params=null)
{
    global $PDO;
    if (isset($params)) {
        $stmt = $PDO->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $PDO->query($query);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function PDO_FetchAssoc($query, $params=null)
{
    global $PDO;
    if (isset($params)) {
        $stmt = $PDO->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $PDO->query($query);
    }
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    $assoc = array();
    foreach ($rows as $row) {
        $assoc[$row[0]] = $row[1];
    }
    return $assoc;
}
function PDO_Execute($query, $params=null)
{
    global $PDO;
    if (isset($params)) {
        $stmt = $PDO->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } else {
        return $PDO->query($query);
    }
}
function PDO_LastInsertId()
{
    global $PDO;
    return $PDO->lastInsertId();
}
function PDO_ErrorInfo()
{
    global $PDO;
    return $PDO->errorInfo();
}

?>
