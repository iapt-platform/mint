<?php
require_once __DIR__."/controller/sent_prs.php";


$SentPr = new CtlSentPr;

switch ($_REQUEST["op"]) {
	case 'index':
		# get
		$SentPr->index();
		break;
	case 'create':
		# post
		$SentPr->create();
		break;
	case 'show':
		# get
		$SentPr->show();
		break;	
	case 'update':
		# post
		$SentPr->update();
		break;	
	case 'delete':
		# get
		$SentPr->delete();
		break;	
	default:
		# code...
		break;
}



