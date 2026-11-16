<?php

namespace Database\Factories;

use App\Models\AgentRun;
use App\Models\AgentThread;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AgentRun>
 */
class AgentRunFactory extends Factory
{
    protected $model = AgentRun::class;

    /**
     * 归属字段（user_uid / device_id）跟随所属会话。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'run_id' => (string) Str::uuid(),
            'agent_thread_id' => AgentThread::factory(),
            // 账号与所属会话一致
            'account_type' => fn (array $attrs) => AgentThread::find($attrs['agent_thread_id'])?->account_type,
            'account_id' => fn (array $attrs) => AgentThread::find($attrs['agent_thread_id'])?->account_id,
            'question' => fake()->sentence().'?',
            'answer' => fake()->paragraph(),
            'deep' => false,
            'improve' => true,
            'status' => 'ok',
            'user_message_id' => (string) Str::uuid(),
            'assistant_message_id' => (string) Str::uuid(),
        ];
    }
}
