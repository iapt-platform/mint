<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class MockOpenAIController extends Controller
{
    /**
     * 模拟 Chat Completions API
     */
    public function chatCompletions(Request $request): JsonResponse
    {
        // 随机延迟
        $this->randomDelay($request->query('delay', 'h'));

        // 随机返回错误
        if ($errorResponse = $this->randomError($request->query('error', "h"))) {
            return $errorResponse;
        }

        $model = $request->input('model', 'gpt-3.5-turbo');
        $messages = $request->input('messages', []);

        return response()->json([
            'id' => 'chatcmpl-' . Str::random(29),
            'object' => 'chat.completion',
            'created' => time(),
            'model' => $model,
            'choices' => [
                [
                    'index' => 0,
                    'message' => [
                        'role' => 'assistant',
                        'content' => $this->generateMockResponse($messages)
                    ],
                    'finish_reason' => 'stop'
                ]
            ],
            'usage' => [
                'prompt_tokens' => rand(10, 100),
                'completion_tokens' => rand(20, 200),
                'total_tokens' => rand(30, 300)
            ]
        ]);
    }

    /**
     * 模拟 Completions API
     */
    public function completions(Request $request): JsonResponse
    {
        // 随机延迟
        $this->randomDelay($request->query('delay', 'h'));

        // 随机返回错误
        if ($errorResponse = $this->randomError($request->query('error', "h"))) {
            return $errorResponse;
        }

        $model = $request->input('model', 'text-davinci-003');
        $prompt = $request->input('prompt', '');

        return response()->json([
            'id' => 'cmpl-' . Str::random(29),
            'object' => 'text_completion',
            'created' => time(),
            'model' => $model,
            'choices' => [
                [
                    'text' => $this->generateMockTextResponse($prompt),
                    'index' => 0,
                    'logprobs' => null,
                    'finish_reason' => 'stop'
                ]
            ],
            'usage' => [
                'prompt_tokens' => rand(10, 100),
                'completion_tokens' => rand(20, 200),
                'total_tokens' => rand(30, 300)
            ]
        ]);
    }

    /**
     * 模拟 Models API
     */
    public function models(Request $request): JsonResponse
    {

        return response()->json([
            'object' => 'list',
            'data' => [
                [
                    'id' => 'gpt-4',
                    'object' => 'model',
                    'created' => 1687882411,
                    'owned_by' => 'openai'
                ],
                [
                    'id' => 'gpt-3.5-turbo',
                    'object' => 'model',
                    'created' => 1677610602,
                    'owned_by' => 'openai'
                ],
                [
                    'id' => 'text-davinci-003',
                    'object' => 'model',
                    'created' => 1669599635,
                    'owned_by' => 'openai-internal'
                ]
            ]
        ]);
    }

    /**
     * 随机延迟
     */
    private function randomDelay(string $level): void
    {
        switch ($level) {
            case 'l':
                sleep(1);
                break;
            case 'm':
                sleep(rand(1, 3));
                break;
            case 'h':
                // 90% 概率 1-3秒延迟
                // 10% 概率 60-100秒延迟
                if (rand(1, 100) <= 10) {
                    sleep(rand(60, 100));
                } else {
                    sleep(rand(1, 3));
                }
                break;
            default:
                break;
        }
    }

    /**
     * 随机返回错误响应
     */
    private function randomError(string $level): ?JsonResponse
    {
        switch ($level) {
            case 'l':
                if (rand(1, 100) <= 10) {
                    return $this->rateLimitError();
                }
                break;
            case 'm':
                if (rand(1, 100) <= 20) {
                    return $this->rateLimitError();
                }
                break;
            case 'h':
                // 20% 概率返回三种错误
                if (rand(1, 100) <= 20) {
                    $errorType = rand(1, 3);
                    switch ($errorType) {
                        case 1:
                            return $this->badRequestError();
                        case 2:
                            return $this->internalServerError();
                        case 3:
                            return $this->rateLimitError();
                    }
                }
                break;
            default:
                return null;
                break;
        }
        return null;
    }

    /**
     * 400 错误响应
     */
    private function badRequestError(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Invalid request: missing required parameter',
                'type' => 'invalid_request_error',
                'param' => null,
                'code' => null
            ]
        ], 400);
    }

    /**
     * 500 错误响应
     */
    private function internalServerError(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'The server had an error while processing your request. Sorry about that!',
                'type' => 'server_error',
                'param' => null,
                'code' => null
            ]
        ], 500);
    }

    /**
     * 429 限流错误响应
     */
    private function rateLimitError(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => 'Rate limit reached for requests',
                'type' => 'requests',
                'param' => null,
                'code' => 'rate_limit_exceeded'
            ]
        ], 429);
    }

    /**
     * 生成模拟聊天响应
     */
    private function generateMockResponse(array $messages): string
    {
        $responses = [
            "这是一个模拟的AI响应。我正在模拟OpenAI的API服务器。",
            "感谢您的问题！这是一个测试响应，用于模拟真实的AI助手。",
            "我是一个模拟的AI助手。您的请求已被处理，这是模拟生成的回复。",
            "模拟Hello! This is a mock response from the simulated OpenAI API server.",
            "模拟Thank you for your message. This is a simulated response for testing purposes.",
            "模拟I understand your question. This is a mock reply generated by the test API server.",
        ];

        return $responses[array_rand($responses)] . " (响应时间: " . date('Y-m-d H:i:s') . ")";
    }

    /**
     * 生成模拟文本补全响应
     */
    private function generateMockTextResponse(string $prompt): string
    {
        $responses = [
            " 这是对您提示的模拟补全回复。",
            " Mock completion response for your prompt.",
            " 模拟的文本补全结果，用于测试目的。",
            " This is a simulated text completion.",
            " 基于您的输入生成的模拟响应。",
        ];

        return $responses[array_rand($responses)];
    }
}
