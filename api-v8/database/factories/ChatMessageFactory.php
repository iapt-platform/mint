<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChatMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uid' => (string) Str::uuid(),
            'chat_id' => Chat::factory(),
            'parent_id' => null,
            'session_id' => (string) Str::uuid(),
            'role' => $this->faker->randomElement(['user', 'assistant', 'tool']),
            'content' => $this->faker->paragraph,
            'model_id' => null,
            'tool_calls' => null,
            'tool_call_id' => null,
            'is_active' => true,
        ];
    }

    public function user()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'user',
                'content' => $this->faker->sentence,
                'model_name' => null,
                'tool_calls' => null,
                'tool_call_id' => null,
            ];
        });
    }

    public function assistant()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'assistant',
                'content' => $this->faker->paragraph,
                'model_name' => $this->faker->randomElement(['gpt-4', 'gpt-3.5-turbo', 'claude-3']),
                'tool_calls' => null,
                'tool_call_id' => null,
            ];
        });
    }

    public function tool()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'tool',
                'content' => $this->faker->paragraph,
                'model_name' => null,
                'tool_calls' => null,
                'tool_call_id' => 'call_' . $this->faker->randomNumber(8),
            ];
        });
    }

    public function withToolCalls()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => [
                    [
                        'id' => 'call_' . $this->faker->randomNumber(8),
                        'function' => $this->faker->randomElement(['get_weather', 'search_web', 'calculate']),
                        'arguments' => [
                            'query' => $this->faker->sentence,
                            'city' => $this->faker->city
                        ]
                    ]
                ]
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function withMetadata($metadata = null)
    {
        return $this->state(function (array $attributes) use ($metadata) {
            return [
                'metadata' => $metadata ?: [
                    'temperature' => $this->faker->randomFloat(2, 0, 2),
                    'tokens' => $this->faker->numberBetween(50, 500),
                    'max_tokens' => $this->faker->numberBetween(500, 2000),
                    'model_version' => $this->faker->randomElement(['1.0', '1.1', '2.0'])
                ],
            ];
        });
    }

    public function withEditor($editorId = null)
    {
        return $this->state(function (array $attributes) use ($editorId) {
            return [
                'editor_id' => $editorId ?: (string) Str::uuid(),
            ];
        });
    }
}
