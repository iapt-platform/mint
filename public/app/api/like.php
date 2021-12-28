<?php
require_once "../db/like.php";
require_once "../redis/function.php";
require_once "../public/function.php";

$model = new Like(redis_connect());

switch ($_REQUEST["_method"]) {
	case 'index':
		# get
		$model->index();
		break;
	case 'list':
		# post
		$model->list();
		break;
	case 'create':
		# post
		$model->create();
		break;
	case 'show':
		# get
		$model->show();
		break;	
	case 'update':
		# post
		$model->update();
		break;	
	case 'delete':
		# get
		$model->delete();
		break;	
	default:
		# code...
		break;
}


?>