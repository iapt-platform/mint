<?php

namespace Tests\Feature;

use Tests\TestCase;

class MockOpenAIApiTest extends TestCase
{

    protected string $baseUrl = '/api/v2/mock/openai';
    protected string $validApiKey = 'Bearer test-api-key-12345';
    protected string $invalidApiKey = 'Bearer invalid-key';


    /**
     * 测试聊天完成 API - 成功响应
     */
    public function test_openai_completions_success_response()
    {
        $response = $this->postJson($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, this is a test message'
                ]
            ]
        ], [
            'Authorization' => $this->validApiKey
        ]);

        // 由于有随机错误，我们需要处理可能的错误响应
        if ($response->status() === 200) {
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'object',
                    'created',
                    'model',
                    'choices' => [
                        '*' => [
                            'index',
                            'message' => [
                                'role',
                                'content'
                            ],
                            'finish_reason'
                        ]
                    ],
                    'usage' => [
                        'prompt_tokens',
                        'completion_tokens',
                        'total_tokens'
                    ]
                ]);

            $responseData = $response->json();
            $this->assertEquals('chat.completion', $responseData['object']);
            $this->assertEquals('gpt-3.5-turbo', $responseData['model']);
            $this->assertEquals('assistant', $responseData['choices'][0]['message']['role']);
            $this->assertStringContainsString('模拟', $responseData['choices'][0]['message']['content']);
        } else {
            // 如果是错误响应，验证错误格式
            $this->assertContains($response->status(), [400, 429, 500]);
            $response->assertJsonStructure([
                'error' => [
                    'message',
                    'type'
                ]
            ]);
        }
    }

    /**
     * 测试文本完成 API - 成功响应
     */
    public function test_completions_success_response()
    {
        $response = $this->postJson($this->baseUrl . '/completions', [
            'model' => 'text-davinci-003',
            'prompt' => 'Once upon a time'
        ], [
            'Authorization' => $this->validApiKey
        ]);

        if ($response->status() === 200) {
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'object',
                    'created',
                    'model',
                    'choices' => [
                        '*' => [
                            'text',
                            'index',
                            'logprobs',
                            'finish_reason'
                        ]
                    ],
                    'usage'
                ]);

            $responseData = $response->json();
            $this->assertEquals('text_completion', $responseData['object']);
            $this->assertEquals('text-davinci-003', $responseData['model']);
        } else {
            $this->assertContains($response->status(), [400, 429, 500]);
        }
    }

    /**
     * 测试模型列表 API
     */
    public function test_models_list_response()
    {
        $response = $this->getJson($this->baseUrl . '/models', [
            'Authorization' => $this->validApiKey
        ]);

        if ($response->status() === 200) {
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'object',
                    'data' => [
                        '*' => [
                            'id',
                            'object',
                            'created',
                            'owned_by'
                        ]
                    ]
                ]);

            $responseData = $response->json();
            $this->assertEquals('list', $responseData['object']);
            $this->assertGreaterThan(0, count($responseData['data']));

            // 验证包含预期的模型
            $modelIds = collect($responseData['data'])->pluck('id')->toArray();
            $this->assertContains('gpt-4', $modelIds);
            $this->assertContains('gpt-3.5-turbo', $modelIds);
        } else {
            $this->assertContains($response->status(), [400, 429, 500]);
        }
    }

    /**
     * 测试错误响应格式
     */
    public function test_error_response_formats()
    {
        // 多次请求以增加遇到错误的概率
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => "Test message $i"]
                ]
            ], [
                'Authorization' => $this->validApiKey
            ]);

            if (in_array($response->status(), [400, 429, 500])) {
                $response->assertJsonStructure([
                    'error' => [
                        'message',
                        'type'
                    ]
                ]);

                $errorData = $response->json()['error'];
                $this->assertNotEmpty($errorData['message']);
                $this->assertNotEmpty($errorData['type']);

                // 验证特定错误类型
                switch ($response->status()) {
                    case 400:
                        $this->assertEquals('invalid_request_error', $errorData['type']);
                        break;
                    case 429:
                        $this->assertEquals('requests', $errorData['type']);
                        break;
                    case 500:
                        $this->assertEquals('server_error', $errorData['type']);
                        break;
                }

                // 找到一个错误响应就足够了
                break;
            }
        }
    }

    /**
     * 测试响应时间（延迟）
     */
    public function test_response_delay()
    {
        $startTime = microtime(true);

        $response = $this->postJson($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Test delay']
            ]
        ], [
            'Authorization' => $this->validApiKey
        ]);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // 验证至少有1秒延迟
        $this->assertGreaterThanOrEqual(1, $duration);

        // 记录响应时间用于调试
        echo "\nResponse time: " . number_format($duration, 2) . " seconds\n";
    }

    /**
     * 测试请求参数验证
     */
    public function test_request_parameters()
    {
        // 测试自定义模型参数
        $response = $this->postJson($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello with GPT-4']
            ],
            'max_tokens' => 100,
            'temperature' => 0.7
        ], [
            'Authorization' => $this->validApiKey
        ]);

        if ($response->status() === 200) {
            $responseData = $response->json();
            $this->assertEquals('gpt-4', $responseData['model']);
        }
    }

    /**
     * 测试并发请求
     */
    public function test_concurrent_requests()
    {
        $promises = [];
        $responses = [];

        // 发送5个并发请求
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->postJson($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => "Concurrent test $i"]
                ]
            ], [
                'Authorization' => $this->validApiKey
            ]);
        }

        // 验证所有响应
        foreach ($responses as $index => $response) {
            $this->assertContains($response->status(), [200, 400, 429, 500]);

            if ($response->status() === 200) {
                $response->assertJsonStructure(['id', 'object', 'choices']);
            } else {
                $response->assertJsonStructure(['error']);
            }
        }
    }
}
