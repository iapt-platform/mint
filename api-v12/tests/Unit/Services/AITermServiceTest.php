<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AITermService;

class AITermServiceTest extends TestCase
{
    public function test_real_llm_generation()
    {


        // ===== 创建 Service =====
        $service = app(AITermService::class);
        $service->setModel('70259783-6053-4a6e-adda-506031b0cc22');

        // ===== 执行 =====
        $result = $service->create('f3ba16e5-862d-49c4-b5b0-39ab8b8ca4f4');

        // ===== 调试输出（建议保留）=====
        dump($result);

        // ===== 断言（关键）=====
        $this->assertNotEmpty($result);

        // Markdown 结构
        $this->assertStringContainsString('##', $result);

        // 引用格式（核心能力）
        $this->assertMatchesRegularExpression('/\{\{para\|id=\d+-\d+\|/', $result);

        // 脚注
        $this->assertMatchesRegularExpression('/\[\d+\]/', $result);
    }
}
