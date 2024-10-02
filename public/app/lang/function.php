<?php 
class lang_enum{
    public $arrLang; //语言列表
    public function __construct() {
        $this->arrLang=json_decode(file_get_contents("../lang/lang_list.json"));
    }
    
    public function getName($id){
        foreach ($this->arrLang as $key => $value) {
            if($value->code==$id){
                return array("name"=>$value->name,"english"=>$value->english);
            }
        }
        return array("name"=>$id,"english"=>"unknow");
    }
}

?>