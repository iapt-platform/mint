<?php

namespace App\Services;

/**
 * 控制器中
public function convert(PaliTransliterationService $pali)
{
    $result = $pali->myanmarToRoman('သမ္မာ');
}

// 或者使用 app() 助手函数
$pali = app(PaliTransliterationService::class);
$result = $pali->thaiToRoman('สมฺมา');
 */
class PaliTransliterationService
{
    /**
     * 缅文字母映射表
     */
    private const MYANMAR_CHARS = [
        "ႁႏၵ",
        "ခ္",
        "ဃ္",
        "ဆ္",
        "ဈ္",
        "ည္",
        "ဌ္",
        "ဎ္",
        "ထ္",
        "ဓ္",
        "ဖ္",
        "ဘ္",
        "က္",
        "ဂ္",
        "စ္",
        "ဇ္",
        "ဉ္",
        "ဠ္",
        "ဋ္",
        "ဍ္",
        "ဏ္",
        "တ္",
        "ဒ္",
        "န္",
        "ဟ္",
        "ပ္",
        "ဗ္",
        "မ္",
        "ယ္",
        "ရ္",
        "လ္",
        "ဝ္",
        "သ္",
        "င္",
        "င်္",
        "ဿ",
        "ခ",
        "ဃ",
        "ဆ",
        "ဈ",
        "စျ",
        "ည",
        "ဌ",
        "ဎ",
        "ထ",
        "ဓ",
        "ဖ",
        "ဘ",
        "က",
        "ဂ",
        "စ",
        "ဇ",
        "ဉ",
        "ဠ",
        "ဋ",
        "ဍ",
        "ဏ",
        "တ",
        "ဒ",
        "န",
        "ဟ",
        "ပ",
        "ဗ",
        "မ",
        "ယ",
        "ရ",
        "႐",
        "လ",
        "ဝ",
        "သ",
        "aျ္",
        "aွ္",
        "aြ္",
        "aြ",
        "ၱ",
        "ၳ",
        "ၵ",
        "ၶ",
        "ၬ",
        "ၭ",
        "ၠ",
        "ၡ",
        "ၢ",
        "ၣ",
        "ၸ",
        "ၹ",
        "ၺ",
        "႓",
        "ၥ",
        "ၧ",
        "ၨ",
        "ၩ",
        "်",
        "ျ",
        "ႅ",
        "ၼ",
        "ွ",
        "ႇ",
        "ႆ",
        "ၷ",
        "ၲ",
        "႒",
        "႗",
        "ၯ",
        "ၮ",
        "႑",
        "kaၤ",
        "gaၤ",
        "khaၤ",
        "ghaၤ",
        "aှ",
        "aိံ",
        "aုံ",
        "aော",
        "aေါ",
        "aအံ",
        "aဣံ",
        "aဥံ",
        "aံ",
        "aာ",
        "aါ",
        "aိ",
        "aီ",
        "aု",
        "aဳ",
        "aူ",
        "aေ",
        "အါ",
        "အာ",
        "အ",
        "ဣ",
        "ဤ",
        "ဥ",
        "ဦ",
        "ဧ",
        "ဩ",
        "ႏ",
        "ၪ",
        "a္",
        "္",
        "aံ",
        "ေss",
        "ေkh",
        "ေgh",
        "ေch",
        "ေjh",
        "ေññ",
        "ေṭh",
        "ေḍh",
        "ေth",
        "ေdh",
        "ေph",
        "ေbh",
        "ေk",
        "ေg",
        "ေc",
        "ေj",
        "ေñ",
        "ေḷ",
        "ေṭ",
        "ေḍ",
        "ေṇ",
        "ေt",
        "ေd",
        "ေn",
        "ေh",
        "ေp",
        "ေb",
        "ေm",
        "ေy",
        "ေr",
        "ေl",
        "ေv",
        "ေs",
        "ေy",
        "ေv",
        "ေr",
        "ea",
        "eā",
        "၁",
        "၂",
        "၃",
        "၄",
        "၅",
        "၆",
        "၇",
        "၈",
        "၉",
        "၀",
        "း",
        "့",
        "။",
        "၊"
    ];

    /**
     * 罗马巴利字母映射表
     */
    private const ROMAN_CHARS = [
        "ndra",
        "kh",
        "gh",
        "ch",
        "jh",
        "ññ",
        "ṭh",
        "ḍh",
        "th",
        "dh",
        "ph",
        "bh",
        "k",
        "g",
        "c",
        "j",
        "ñ",
        "ḷ",
        "ṭ",
        "ḍ",
        "ṇ",
        "t",
        "d",
        "n",
        "h",
        "p",
        "b",
        "m",
        "y",
        "r",
        "l",
        "v",
        "s",
        "ṅ",
        "ṅ",
        "ssa",
        "kha",
        "gha",
        "cha",
        "jha",
        "jha",
        "ñña",
        "ṭha",
        "ḍha",
        "tha",
        "dha",
        "pha",
        "bha",
        "ka",
        "ga",
        "ca",
        "ja",
        "ña",
        "ḷa",
        "ṭa",
        "ḍa",
        "ṇa",
        "ta",
        "da",
        "na",
        "ha",
        "pa",
        "ba",
        "ma",
        "ya",
        "ra",
        "ra",
        "la",
        "va",
        "sa",
        "ya",
        "va",
        "ra",
        "ra",
        "្ta",
        "្tha",
        "្da",
        "្dha",
        "្ṭa",
        "្ṭha",
        "្ka",
        "្kha",
        "្ga",
        "្gha",
        "្pa",
        "្pha",
        "្ba",
        "្bha",
        "្ca",
        "្cha",
        "្ja",
        "្jha",
        "្a",
        "្ya",
        "្la",
        "្ma",
        "្va",
        "្ha",
        "ssa",
        "na",
        "ta",
        "ṭṭha",
        "ṭṭa",
        "ḍḍha",
        "ḍḍa",
        "ṇḍa",
        "ṅka",
        "ṅga",
        "ṅkha",
        "ṅgha",
        "ha",
        "iṃ",
        "uṃ",
        "o",
        "o",
        "aṃ",
        "iṃ",
        "uṃ",
        "aṃ",
        "ā",
        "ā",
        "i",
        "ī",
        "u",
        "u",
        "ū",
        "e",
        "ā",
        "ā",
        "a",
        "i",
        "ī",
        "u",
        "ū",
        "e",
        "o",
        "n",
        "ñ",
        "",
        "",
        "aṃ",
        "sse",
        "khe",
        "ghe",
        "che",
        "jhe",
        "ññe",
        "ṭhe",
        "ḍhe",
        "the",
        "dhe",
        "phe",
        "bhe",
        "ke",
        "ge",
        "ce",
        "je",
        "ñe",
        "ḷe",
        "ṭe",
        "ḍe",
        "ṇe",
        "te",
        "de",
        "ne",
        "he",
        "pe",
        "be",
        "me",
        "ye",
        "re",
        "le",
        "ve",
        "se",
        "ye",
        "ve",
        "re",
        "e",
        "o",
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        "0",
        "\"",
        "'",
        "．",
        "，"
    ];

    /**
     * 泰文字母映射表
     */
    private const THAI_CHARS = [
        "นฺทฺร",
        "ขฺ",
        "ฆฺ",
        "ฉฺ",
        "ฌฺ",
        "ญฺ",
        "ฐฺ",
        "ฑฺ",
        "ถฺ",
        "ธฺ",
        "ผฺ",
        "ภฺ",
        "กฺ",
        "คฺ",
        "จฺ",
        "ชฺ",
        "ญฺ",
        "ฬฺ",
        "ฏฺ",
        "ฑฺ",
        "ณฺ",
        "ตฺ",
        "ทฺ",
        "นฺ",
        "หฺ",
        "ปฺ",
        "พฺ",
        "มฺ",
        "ยฺ",
        "รฺ",
        "ลฺ",
        "วฺ",
        "สฺ",
        "งฺ",
        "งฺ",
        "สฺส",
        "ข",
        "ฆ",
        "ฉ",
        "ฌ",
        "ฌ",
        "ญฺญ",
        "ฐ",
        "ฑ",
        "ถ",
        "ธ",
        "ผ",
        "ภ",
        "ก",
        "ค",
        "จ",
        "ช",
        "ญ",
        "ฬ",
        "ฏ",
        "ฑ",
        "ณ",
        "ต",
        "ท",
        "น",
        "ห",
        "ป",
        "พ",
        "ม",
        "ย",
        "ร",
        "ร",
        "ล",
        "ว",
        "ส",
        "ฺย",
        "ฺว",
        "ฺร",
        "ร",
        "ตฺต",
        "ตฺถ",
        "ทฺท",
        "ทฺธ",
        "ฏฺฏ",
        "ฏฺฐ",
        "กฺก",
        "ขฺข",
        "คฺค",
        "ฆฺฆ",
        "ปฺป",
        "ผฺผ",
        "พฺพ",
        "ภฺภ",
        "จฺจ",
        "ฉฺฉ",
        "ชฺช",
        "ฌฺฌ",
        "ฺ",
        "ฺย",
        "ฺล",
        "ฺม",
        "ฺว",
        "ฺห",
        "สฺส",
        "น",
        "ต",
        "ฏฺฐ",
        "ฏฺฏ",
        "ฑฺฒ",
        "ฑฺฑ",
        "ณฺฑ",
        "งฺก",
        "งฺค",
        "งฺข",
        "งฺฆ",
        "ห",
        "ิํ",
        "ุํ",
        "โอ",
        "โอ",
        "อํ",
        "อิํ",
        "อุํ",
        "ํ",
        "า",
        "า",
        "ิ",
        "ี",
        "ุ",
        "ุ",
        "ู",
        "เ",
        "อา",
        "อา",
        "อ",
        "อิ",
        "อี",
        "อุ",
        "อู",
        "เอ",
        "โอ",
        "น",
        "ญ",
        "",
        "ฺ",
        "ํ",
        "เสฺส",
        "เข",
        "เฆ",
        "เฉ",
        "เฌ",
        "เญฺญ",
        "เฐ",
        "เฑ",
        "เถ",
        "เธ",
        "เผ",
        "เภ",
        "เก",
        "เค",
        "เจ",
        "เช",
        "เญ",
        "เฬ",
        "เฏ",
        "เฑ",
        "เณ",
        "เต",
        "เท",
        "เน",
        "เห",
        "เป",
        "เพ",
        "เม",
        "เย",
        "เร",
        "เล",
        "เว",
        "เส",
        "เย",
        "เว",
        "เร",
        "เอ",
        "โอ",
        "๑",
        "๒",
        "๓",
        "๔",
        "๕",
        "๖",
        "๗",
        "๘",
        "๙",
        "๐",
        "ํ",
        "ฺ",
        "ฯ",
        "ฯลฯ"
    ];

    /**
     * 缅文转罗马巴利
     *
     * @param string $input
     * @return string
     */
    public function myanmarToRoman(string $input): string
    {
        return str_replace(self::MYANMAR_CHARS, self::ROMAN_CHARS, $input);
    }

    /**
     * 罗马巴利转缅文
     *
     * @param string $input
     * @return string
     */
    public function romanToMyanmar(string $input): string
    {
        // 手动构建映射数组，遇到重复的键时保留第一个
        $mapping = [];
        foreach (self::ROMAN_CHARS as $index => $roman) {
            if (!isset($mapping[$roman])) {
                $mapping[$roman] = self::MYANMAR_CHARS[$index];
            }
        }

        // 按键长度降序排序，优先匹配较长的字符串
        uksort($mapping, function ($a, $b) {
            $lenDiff = strlen($b) - strlen($a);
            if ($lenDiff !== 0) {
                return $lenDiff;
            }
            return strcmp($a, $b);
        });

        return str_replace(array_keys($mapping), array_values($mapping), $input);
    }

    /**
     * 泰文转罗马巴利
     *
     * @param string $input
     * @return string
     */
    public function thaiToRoman(string $input): string
    {
        return str_replace(self::THAI_CHARS, self::ROMAN_CHARS, $input);
    }

    /**
     * 罗马巴利转泰文
     *
     * @param string $input
     * @return string
     */
    public function romanToThai(string $input): string
    {
        // 手动构建映射数组，遇到重复的键时保留第一个
        $mapping = [];
        foreach (self::ROMAN_CHARS as $index => $roman) {
            if (!isset($mapping[$roman])) {
                $mapping[$roman] = self::THAI_CHARS[$index];
            }
        }

        // 按键长度降序排序，优先匹配较长的字符串
        uksort($mapping, function ($a, $b) {
            $lenDiff = strlen($b) - strlen($a);
            if ($lenDiff !== 0) {
                return $lenDiff;
            }
            return strcmp($a, $b);
        });

        return str_replace(array_keys($mapping), array_values($mapping), $input);
    }

    /**
     * 缅文转泰文
     *
     * @param string $input
     * @return string
     */
    public function myanmarToThai(string $input): string
    {
        $roman = $this->myanmarToRoman($input);
        return $this->romanToThai($roman);
    }

    /**
     * 泰文转缅文
     *
     * @param string $input
     * @return string
     */
    public function thaiToMyanmar(string $input): string
    {
        $roman = $this->thaiToRoman($input);
        return $this->romanToMyanmar($roman);
    }

    /**
     * 自动检测并转换为罗马巴利
     *
     * @param string $input
     * @return string
     */
    public function toRoman(string $input): string
    {
        // 检测是否包含缅文字符
        if (preg_match('/[\x{1000}-\x{109F}]/u', $input)) {
            return $this->myanmarToRoman($input);
        }

        // 检测是否包含泰文字符
        if (preg_match('/[\x{0E00}-\x{0E7F}]/u', $input)) {
            return $this->thaiToRoman($input);
        }

        // 默认返回原文
        return $input;
    }
}
