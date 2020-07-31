<?php
$FileName = $_GET["filename"];
echo file_get_contents($FileName);

?>