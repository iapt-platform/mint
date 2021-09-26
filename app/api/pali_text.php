<?php
require_once "../db/pali_text.php";
require_once "../redis/function.php";
require_once "../public/function.php";

$model = new PaliText(redis_connect());

switch ($_REQUEST["_method"]) {
	case 'index':
		# get
		$model->index();
		break;
	case 'show':
		# get
		$model->show();
		break;		
	default:
		# code...
		break;
}


?>