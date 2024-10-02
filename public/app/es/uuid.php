<?php
require_once '../public/function.php';
$uuid = str_replace("-", "", UUID::v4());
$str = gmp_strval(gmp_init($uuid, 16), 62);
echo $str;
