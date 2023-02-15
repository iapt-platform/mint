<?php
namespace App\Tools;

class Tools{

    public static function getWordEn($strIn)
        {
            $out = str_replace(["ā","ī","ū","ṅ","ñ","ṭ","ḍ","ṇ","ḷ","ṃ"],
                            ["a","i","u","n","n","t","d","n","l","m"], $strIn);
            return ($out);
        }
}
