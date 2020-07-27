<?php
$csvDir = "../user/My Document/";
$csvFileName = $_GET["filename"];
echo file_get_contents($csvFileName);

?>