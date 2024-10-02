<?php
    require_once '../ucenter/setting_function.php';
echo json_encode(get_setting(), JSON_UNESCAPED_UNICODE);
?>