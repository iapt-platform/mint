<?php
require_once __DIR__."/controller/user_dicts.php";


$UserDict = new CtlUserDict;

switch ($_REQUEST["op"]) {
	case 'index':
		# get
		$UserDict->index();
		break;
	case 'create':
		# post
		$UserDict->create();
		break;
	case 'show':
		# get
		$UserDict->show();
		break;	
	case 'update':
		# post
		$UserDict->update();
		break;	
	case 'delete':
		# get
		$UserDict->delete();
		break;	
	default:
		# code...
		break;
}



