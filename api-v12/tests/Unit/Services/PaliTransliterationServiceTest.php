<?php

namespace Tests\Unit\Services;

use App\Services\PaliTransliterationService;
use PHPUnit\Framework\TestCase;

class PaliTransliterationServiceTest extends TestCase
{
    private PaliTransliterationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaliTransliterationService();
    }

    /**
     * 测试缅文转罗马巴利
     */
    public function test_myanmar_to_roman_conversion(): void
    {
        // 测试基本辅音
        $this->assertEquals('ka', $this->service->myanmarToRoman('က'));
        $this->assertEquals('kha', $this->service->myanmarToRoman('ခ'));
        $this->assertEquals('ga', $this->service->myanmarToRoman('ဂ'));

        // 测试复杂组合
        $this->assertEquals('ssa', $this->service->myanmarToRoman('ဿ'));
        $this->assertEquals('ndra', $this->service->myanmarToRoman('ႁႏၵ'));

        // 测试元音标记
        $this->assertEquals('ā', $this->service->myanmarToRoman('aာ'));
        $this->assertEquals('i', $this->service->myanmarToRoman('aိ'));
        $this->assertEquals('ī', $this->service->myanmarToRoman('aီ'));

        // 测试数字
        $this->assertEquals('1', $this->service->myanmarToRoman('၁'));
        $this->assertEquals('0', $this->service->myanmarToRoman('၀'));

        // 测试标点符号
        $this->assertEquals('．', $this->service->myanmarToRoman('။'));
        $this->assertEquals('，', $this->service->myanmarToRoman('၊'));
    }

    /**
     * 测试罗马巴利转缅文
     */
    public function test_roman_to_myanmar_conversion(): void
    {
        $this->assertEquals('က', $this->service->romanToMyanmar('ka'));
        $this->assertEquals('ခ', $this->service->romanToMyanmar('kha'));
        $this->assertEquals('ဿ', $this->service->romanToMyanmar('ssa'));
        $this->assertEquals('၁', $this->service->romanToMyanmar('1'));
    }

    /**
     * 测试泰文转罗马巴利
     */
    public function test_thai_to_roman_conversion(): void
    {
        // 测试基本辅音
        $this->assertEquals('ka', $this->service->thaiToRoman('ก'));
        $this->assertEquals('kha', $this->service->thaiToRoman('ข'));
        $this->assertEquals('ga', $this->service->thaiToRoman('ค'));

        // 测试复杂组合
        $this->assertEquals('ssa', $this->service->thaiToRoman('สฺส'));
        $this->assertEquals('ndra', $this->service->thaiToRoman('นฺทฺร'));

        // 测试元音
        $this->assertEquals('ā', $this->service->thaiToRoman('า'));
        $this->assertEquals('i', $this->service->thaiToRoman('ิ'));

        // 测试数字
        $this->assertEquals('1', $this->service->thaiToRoman('๑'));
        $this->assertEquals('0', $this->service->thaiToRoman('๐'));

        // 测试标点符号 - 泰文 ฯ 转换为罗马巴利 ．
        $this->assertEquals('．', $this->service->thaiToRoman('ฯ'));
    }

    /**
     * 测试罗马巴利转泰文
     */
    public function test_roman_to_thai_conversion(): void
    {
        $this->assertEquals('ก', $this->service->romanToThai('ka'));
        $this->assertEquals('ข', $this->service->romanToThai('kha'));
        $this->assertEquals('สฺส', $this->service->romanToThai('ssa'));
        $this->assertEquals('๑', $this->service->romanToThai('1'));
    }

    /**
     * 测试缅文转泰文
     */
    public function test_myanmar_to_thai_conversion(): void
    {
        $this->assertEquals('ก', $this->service->myanmarToThai('က'));
        $this->assertEquals('ข', $this->service->myanmarToThai('ခ'));
        $this->assertEquals('สฺส', $this->service->myanmarToThai('ဿ'));
    }

    /**
     * 测试泰文转缅文
     */
    public function test_thai_to_myanmar_conversion(): void
    {
        $this->assertEquals('က', $this->service->thaiToMyanmar('ก'));
        $this->assertEquals('ခ', $this->service->thaiToMyanmar('ข'));
        $this->assertEquals('ဿ', $this->service->thaiToMyanmar('สฺส'));
    }

    /**
     * 测试自动检测 - 缅文输入
     */
    public function test_auto_detect_myanmar_input(): void
    {
        $input = 'သမ္မာ'; // 缅文
        $result = $this->service->toRoman($input);

        // 验证结果包含罗马字母和可能的变音符号
        $this->assertMatchesRegularExpression('/^[a-zA-Zāīūṅñṭḍṇḷṃ]+$/', $result);
    }

    /**
     * 测试自动检测 - 泰文输入
     */
    public function test_auto_detect_thai_input(): void
    {
        $input = 'สมฺมา'; // 泰文
        $result = $this->service->toRoman($input);

        // 验证结果包含罗马字母和可能的变音符号
        $this->assertMatchesRegularExpression('/^[a-zA-Zāīūṅñṭḍṇḷṃ]+$/', $result);
    }

    /**
     * 测试自动检测 - 罗马文输入（无需转换）
     */
    public function test_auto_detect_roman_input(): void
    {
        $input = 'sammā';
        $result = $this->service->toRoman($input);

        // 应该返回原文
        $this->assertEquals($input, $result);
    }

    /**
     * 测试空字符串
     */
    public function test_empty_string_conversion(): void
    {
        $this->assertEquals('', $this->service->myanmarToRoman(''));
        $this->assertEquals('', $this->service->thaiToRoman(''));
        $this->assertEquals('', $this->service->toRoman(''));
    }

    /**
     * 测试完整单词转换 - 缅文
     */
    public function test_full_word_myanmar_conversion(): void
    {
        // 测试"法"这个词
        $myanmar = 'ဓမ္မ';
        $expected = 'dhamma';

        $this->assertEquals($expected, $this->service->myanmarToRoman($myanmar));
    }

    /**
     * 测试完整单词转换 - 泰文
     */
    public function test_full_word_thai_conversion(): void
    {
        // 测试"法"这个词
        $thai = 'ธมฺม';
        $expected = 'dhamma';

        $this->assertEquals($expected, $this->service->thaiToRoman($thai));
    }

    /**
     * 测试往返转换一致性 - 缅文
     * 注意：由于映射表的特性，往返转换可能不完全一致
     * 这里测试的是转换后再转回能得到有效的缅文
     */
    public function test_myanmar_round_trip_conversion(): void
    {
        $original = 'က';
        $roman = $this->service->myanmarToRoman($original);

        // 验证转换为罗马字母成功
        $this->assertEquals('ka', $roman);

        // 验证可以转回缅文（可能不完全相同）
        $backToMyanmar = $this->service->romanToMyanmar($roman);
        $this->assertNotEmpty($backToMyanmar);

        // 验证再次转换为罗马字母时结果一致
        $this->assertEquals($roman, $this->service->myanmarToRoman($backToMyanmar));
    }

    /**
     * 测试往返转换一致性 - 泰文
     * 注意：由于映射表的特性，往返转换可能不完全一致
     * 这里测试的是转换后再转回能得到有效的泰文
     */
    public function test_thai_round_trip_conversion(): void
    {
        $original = 'ก';
        $roman = $this->service->thaiToRoman($original);

        // 验证转换为罗马字母成功
        $this->assertEquals('ka', $roman);

        // 验证可以转回泰文（可能不完全相同）
        $backToThai = $this->service->romanToThai($roman);
        $this->assertNotEmpty($backToThai);

        // 验证再次转换为罗马字母时结果一致
        $this->assertEquals($roman, $this->service->thaiToRoman($backToThai));
    }

    /**
     * 测试混合内容（包含未映射字符）
     */
    public function test_mixed_content_with_unmapped_characters(): void
    {
        $input = 'က test ခ';
        $result = $this->service->myanmarToRoman($input);

        // 验证缅文被转换，英文保持不变
        $this->assertStringContainsString('test', $result);
        $this->assertStringContainsString('ka', $result);
        $this->assertStringContainsString('kha', $result);
    }

    /**
     * 测试特殊组合字符
     */
    public function test_special_combined_characters(): void
    {
        // 测试鼻音组合
        $this->assertEquals('ṅka', $this->service->myanmarToRoman('kaၤ'));
        $this->assertEquals('ṅga', $this->service->myanmarToRoman('gaၤ'));

        // 测试双辅音
        $this->assertEquals('ṭṭha', $this->service->myanmarToRoman('႒'));
        $this->assertEquals('ṭṭa', $this->service->myanmarToRoman('႗'));
    }
}
