<?php
require_once __DIR__."/../model/sent_prs.php";
require_once __DIR__."/controller.php";


Class CtlSentPr extends Controller{
	public function index(){
		$result = SentPr::find($_GET["id"]);
		echo $result["content"];
	}	
	public function create(){
		$result = SentPr::find($_GET["id"]);
		echo $result["content"];
	}	
		
	public function show(){
		$result = SentPr::find($_GET["id"]);
		if($result){
			$this->ok($result);
		}else{
			$this->error("没有查询到数据");
		}
	}
	public function update(){
		$result = SentPr::find($_GET["id"]);
		echo $result["content"];
	}	
	public function delete(){
		$result = SentPr::find($_GET["id"]);
		echo $result["content"];
	}
} 