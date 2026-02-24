<?php

namespace Tests\Unit\Services;

use App\Services\NissayaParser;
use Tests\TestCase;

/**
 * php artisan test --filter NissayaParserTest
 */
class NissayaParserTest extends TestCase
{
    private NissayaParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new NissayaParser();
    }

    /**
     * 测试标准格式解析
     */
    public function test_parse_standard_format(): void
    {
        $content = "pañcamassa=ပဉ္စမဝဂ်၏\npaṭhame=ပထမသုတ်၌";

        $result = $this->parser->parse($content);

        $this->assertCount(2, $result);
        $this->assertEquals('pañcamassa', $result[0]['original']);
        $this->assertEquals('ပဉ္စမဝဂ်၏', $result[0]['translation']);
        $this->assertEmpty($result[0]['notes']);

        $this->assertEquals('paṭhame', $result[1]['original']);
        $this->assertEquals('ပထမသုတ်၌', $result[1]['translation']);
    }

    /**
     * 测试带单个注释块的格式
     */
    public function test_parse_with_single_note(): void
    {
        $content = "uttānāti=ဥတ္တာနာ-ဟူသည်ကား\n```\nထင်ရှားသော\n```\nappaṭicchannā=ဖုံးကွယ်ခြင်းမရှိသော";

        $result = $this->parser->parse($content);

        $this->assertCount(2, $result);
        $this->assertEquals('uttānāti', $result[0]['original']);
        $this->assertEquals('ဥတ္တာနာ-ဟူသည်ကား', $result[0]['translation']);
        $this->assertCount(1, $result[0]['notes']);
        $this->assertEquals('ထင်ရှားသော', $result[0]['notes'][0]);
    }

    /**
     * 测试特殊情况1: 巴利文和翻译分离,中间有注释
     */
    public function test_parse_separated_pali_and_translation_with_note(): void
    {
        $content = "uttānāti\n```\nထင်ရှားသော\n```\n=ဥတ္တာနာ-ဟူသည်ကား";

        $result = $this->parser->parse($content);

        $this->assertCount(1, $result);
        $this->assertEquals('uttānāti', $result[0]['original']);
        $this->assertContains('ထင်ရှားသော', $result[0]['notes']);
    }

    /**
     * 测试特殊情况2: 等号在上一行
     */
    public function test_parse_with_equal_sign_on_previous_line(): void
    {
        $content = "uttānāti=\n```\nထင်ရှားသော\n```\nဥတ္တာနာ-ဟူသည်ကား";

        $result = $this->parser->parse($content);

        $this->assertCount(1, $result);
        $this->assertEquals('uttānāti', $result[0]['original']);
        $this->assertEquals('ဥတ္တာနာ-ဟူသည်ကား', $result[0]['translation']);
        $this->assertCount(1, $result[0]['notes']);
        $this->assertEquals('ထင်ရှားသော', $result[0]['notes'][0]);
    }

    /**
     * 测试多个注释块
     */
    public function test_parse_with_multiple_notes(): void
    {
        $content = "uttānāti=ဥတ္တာနာ-ဟူသည်ကား\n```\nထင်ရှားသော\n```\n```\n第二个注释\n```";

        $result = $this->parser->parse($content);

        $this->assertCount(1, $result);
        $this->assertEquals('uttānāti', $result[0]['original']);
        $this->assertCount(2, $result[0]['notes']);
        $this->assertEquals('ထင်ရှားသော', $result[0]['notes'][0]);
        $this->assertEquals('第二个注释', $result[0]['notes'][1]);
    }

    /**
     * 测试使用``包裹的注释
     */
    public function test_parse_with_double_backtick_notes(): void
    {
        $content = "uttānāti=ဥတ္တာနာ-ဟူသည်ကား\n``\n注释内容\n``";

        $result = $this->parser->parse($content);

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]['notes']);
        $this->assertEquals('注释内容', $result[0]['notes'][0]);
    }

    /**
     * 测试复杂的混合格式
     */
    public function test_parse_complex_mixed_format(): void
    {
        $content = <<<TEXT
pañcamassa=ပဉ္စမဝဂ်၏
paṭhame=ပထမသုတ်၌
uttānāti=ဥတ္တာနာ-ဟူသည်ကား
```
ထင်ရှားသော
```
appaṭicchannā=ဖုံးကွယ်ခြင်းမရှိသော
``
另一种注释格式
``
dhammā=ဓမ္မာ-ဟူသည်ကား
```
第一个注释
```
```
第二个注释
```
TEXT;

        $result = $this->parser->parse($content);

        $this->assertCount(5, $result);

        // 第一条记录
        $this->assertEquals('pañcamassa', $result[0]['original']);
        $this->assertEmpty($result[0]['notes']);

        // 第二条记录
        $this->assertEquals('paṭhame', $result[1]['original']);

        // 第三条记录 - 有单个注释
        $this->assertEquals('uttānāti', $result[2]['original']);
        $this->assertCount(1, $result[2]['notes']);

        // 第四条记录 - 有``格式注释
        $this->assertEquals('appaṭicchannā', $result[3]['original']);
        $this->assertCount(1, $result[3]['notes']);
        $this->assertEquals('另一种注释格式', $result[3]['notes'][0]);

        // 第五条记录 - 有两个注释
        $this->assertEquals('dhammā', $result[4]['original']);
        $this->assertCount(2, $result[4]['notes']);
        $this->assertEquals('第一个注释', $result[4]['notes'][0]);
        $this->assertEquals('第二个注释', $result[4]['notes'][1]);
    }

    /**
     * 测试空内容
     */
    public function test_parse_empty_content(): void
    {
        $result = $this->parser->parse('');

        $this->assertEmpty($result);
    }

    /**
     * 测试只有空行的内容
     */
    public function test_parse_only_blank_lines(): void
    {
        $result = $this->parser->parse("\n\n\n");

        $this->assertEmpty($result);
    }

    /**
     * 测试文件解析 - Mock Storage
     */
    public function test_parse_file(): void
    {
        // 创建临时测试文件
        $testContent = "pañcamassa=ပဉ္စမဝဂ်၏\npaṭhame=ပထမသုတ်၌";
        $tempFile = tempnam(sys_get_temp_dir(), 'pali_test_');
        file_put_contents($tempFile, $testContent);

        try {
            $result = $this->parser->parseFile($tempFile);

            $this->assertCount(2, $result);
            $this->assertEquals('pañcamassa', $result[0]['original']);
            $this->assertEquals('paṭhame', $result[1]['original']);
        } finally {
            // 清理临时文件
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * 测试文件不存在的情况
     */
    public function test_parse_file_not_found(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('文件不存在');

        $this->parser->parseFile('/path/to/nonexistent/file.txt');
    }

    /**
     * 测试带有多行注释内容的代码块
     */
    public function test_parse_multiline_note_content(): void
    {
        $content = <<<TEXT
uttānāti=ဥတ္တာနာ-ဟူသည်ကား
```
第一行注释
第二行注释
第三行注释
```
TEXT;

        $result = $this->parser->parse($content);

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]['notes']);
        $this->assertStringContainsString('第一行注释', $result[0]['notes'][0]);
        $this->assertStringContainsString('第二行注释', $result[0]['notes'][0]);
        $this->assertStringContainsString('第三行注释', $result[0]['notes'][0]);
    }
}
