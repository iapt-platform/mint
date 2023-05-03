<?php
namespace App\Tools;

class Tools{

    public static function getWordEn($strIn)
        {
            $out = str_replace(["ā","ī","ū","ṅ","ñ","ṭ","ḍ","ṇ","ḷ","ṃ"],
                            ["a","i","u","n","n","t","d","n","l","m"], $strIn);
            return ($out);
        }
    public static function JsonToXml($inArray){
        $xmlObj = simplexml_load_string("<word></word>");
        function convert($array,$xml){
            foreach ($array as $key => $line) {
                # code...
                if(!is_array($line)){
                    $data = $xml->addChild($key,$line);
                }else{
                    if(isset($line['value'])){
                        $value = $line['value'];
                        unset($line['value']);
                    }else{
                        $value = "";
                    }
                    $obj = $xml->addChild($key,$value);
                    if(isset($line['status'])){
                        $obj->addAttribute('status',$line['status']);
                        unset($line['status']);
                    }
                    convert($line,$obj);
                }
            }
            return $xml;
        }
        $xmlDoc = convert($inArray,$xmlObj);
        return $xmlDoc->asXml();
    }
}
