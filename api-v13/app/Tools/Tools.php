<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;

class Tools
{
    public static function zip($zipFile, $files)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($zipFile, \ZipArchive::CREATE);
        if ($res === TRUE) {
            foreach ($files as $key => $value) {
                $zip->addFromString($key, $value);
            }
            $zip->close();
            return true;
        } else {
            return false;
        }
    }
    public static function isStop()
    {
        if (file_exists(base_path('.stop'))) {
            Log::debug('.stop exists');
            return true;
        } else {
            return false;
        }
    }

    public static function getWordEn($strIn)
    {
        $out = str_replace(
            ["ā", "ī", "ū", "ṅ", "ñ", "ṭ", "ḍ", "ṇ", "ḷ", "ṃ"],
            ["a", "i", "u", "n", "n", "t", "d", "n", "l", "m"],
            $strIn
        );
        return ($out);
    }
    public static function PaliReal($inStr): string
    {
        if (!is_string($inStr)) {
            return "";
        }
        $paliLetter = "abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
        $output = [];
        $inStr = strtolower($inStr);
        for ($i = 0; $i < mb_strlen($inStr, "UTF-8"); $i++) {
            # code...
            if (strstr($paliLetter, $inStr[$i]) !== FALSE) {
                $output[] = $inStr[$i];
            }
        }
        return implode('', $output);
    }
    private static function convert($array, $xml)
    {
        foreach ($array as $key => $line) {
            # code...
            if (!is_array($line)) {
                $data = $xml->addChild($key, $line);
            } else {
                if (isset($line['value'])) {
                    $value = $line['value'];
                    unset($line['value']);
                } else {
                    $value = "";
                }
                $obj = $xml->addChild($key, $value);
                if (isset($line['status'])) {
                    $obj->addAttribute('status', $line['status']);
                    unset($line['status']);
                }
                Tools::convert($line, $obj);
            }
        }
        return $xml;
    }
    public static function JsonToXml($inArray)
    {
        $xmlObj = simplexml_load_string("<word></word>");
        $xmlDoc = Tools::convert($inArray, $xmlObj);
        return $xmlDoc->asXml();
    }

    public static function MyToRm($input)
    {
        $my = ["ႁႏၵ", "ခ္", "ဃ္", "ဆ္", "ဈ္", "ည္", "ဌ္", "ဎ္", "ထ္", "ဓ္", "ဖ္", "ဘ္", "က္", "ဂ္", "စ္", "ဇ္", "ဉ္", "ဠ္", "ဋ္", "ဍ္", "ဏ္", "တ္", "ဒ္", "န္", "ဟ္", "ပ္", "ဗ္", "မ္", "ယ္", "ရ္", "လ္", "ဝ္", "သ္", "င္", "င်္", "ဿ", "ခ", "ဃ", "ဆ", "ဈ", "စျ", "ည", "ဌ", "ဎ", "ထ", "ဓ", "ဖ", "ဘ", "က", "ဂ", "စ", "ဇ", "ဉ", "ဠ", "ဋ", "ဍ", "ဏ", "တ", "ဒ", "န", "ဟ", "ပ", "ဗ", "မ", "ယ", "ရ", "႐", "လ", "ဝ", "သ", "aျ္", "aွ္", "aြ္", "aြ", "ၱ", "ၳ", "ၵ", "ၶ", "ၬ", "ၭ", "ၠ", "ၡ", "ၢ", "ၣ", "ၸ", "ၹ", "ၺ", "႓", "ၥ", "ၧ", "ၨ", "ၩ", "်", "ျ", "ႅ", "ၼ", "ွ", "ႇ", "ႆ", "ၷ", "ၲ", "႒", "႗", "ၯ", "ၮ", "႑", "kaၤ", "gaၤ", "khaၤ", "ghaၤ", "aှ", "aိံ", "aုံ", "aော", "aေါ", "aအံ", "aဣံ", "aဥံ", "aံ", "aာ", "aါ", "aိ", "aီ", "aု", "aဳ", "aူ", "aေ", "အါ", "အာ", "အ", "ဣ", "ဤ", "ဥ", "ဦ", "ဧ", "ဩ", "ႏ", "ၪ", "a္", "္", "aံ", "ေss", "ေkh", "ေgh", "ေch", "ေjh", "ေññ", "ေṭh", "ေḍh", "ေth", "ေdh", "ေph", "ေbh", "ေk", "ေg", "ေc", "ေj", "ေñ", "ေḷ", "ေṭ", "ေḍ", "ေṇ", "ေt", "ေd", "ေn", "ေh", "ေp", "ေb", "ေm", "ေy", "ေr", "ေl", "ေv", "ေs", "ေy", "ေv", "ေr", "ea", "eā", "၁", "၂", "၃", "၄", "၅", "၆", "၇", "၈", "၉", "၀", "း", "့", "။", "၊"];
        $en = ["ndra", "kh", "gh", "ch", "jh", "ññ", "ṭh", "ḍh", "th", "dh", "ph", "bh", "k", "g", "c", "j", "ñ", "ḷ", "ṭ", "ḍ", "ṇ", "t", "d", "n", "h", "p", "b", "m", "y", "r", "l", "v", "s", "ṅ", "ṅ", "ssa", "kha", "gha", "cha", "jha", "jha", "ñña", "ṭha", "ḍha", "tha", "dha", "pha", "bha", "ka", "ga", "ca", "ja", "ña", "ḷa", "ṭa", "ḍa", "ṇa", "ta", "da", "na", "ha", "pa", "ba", "ma", "ya", "ra", "ra", "la", "va", "sa", "ya", "va", "ra", "ra", "္ta", "္tha", "္da", "္dha", "္ṭa", "္ṭha", "္ka", "္kha", "္ga", "္gha", "္pa", "္pha", "္ba", "္bha", "္ca", "္cha", "္ja", "္jha", "္a", "္ya", "္la", "္ma", "္va", "္ha", "ssa", "na", "ta", "ṭṭha", "ṭṭa", "ḍḍha", "ḍḍa", "ṇḍa", "ṅka", "ṅga", "ṅkha", "ṅgha", "ha", "iṃ", "uṃ", "o", "o", "aṃ", "iṃ", "uṃ", "aṃ", "ā", "ā", "i", "ī", "u", "u", "ū", "e", "ā", "ā", "a", "i", "ī", "u", "ū", "e", "o", "n", "ñ", "", "", "aṃ", "sse", "khe", "ghe", "che", "jhe", "ññe", "ṭhe", "ḍhe", "the", "dhe", "phe", "bhe", "ke", "ge", "ce", "je", "ñe", "ḷe", "ṭe", "ḍe", "ṇe", "te", "de", "ne", "he", "pe", "be", "me", "ye", "re", "le", "ve", "se", "ye", "ve", "re", "e", "o", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "”", "’", "．", "，"];
        return str_replace($my, $en, $input);
    }
}
