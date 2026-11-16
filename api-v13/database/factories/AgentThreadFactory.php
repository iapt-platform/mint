<?php

namespace Database\Factories;

use App\Models\AgentThread;
use App\Services\V3\AgentAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AgentThread>
 */
class AgentThreadFactory extends Factory
{
    protected $model = AgentThread::class;

    /**
     * 默认是游客会话；登录用户的会话用 {@see self::forUser()}。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thread_id' => (string) Str::uuid(),
            'account_type' => 'guest',
            'account_id' => (string) Str::uuid(),
            'title' => fake()->sentence(4),
            'client' => 'mobile',
            'last_run_at' => now(),
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['archived_at' => now()]);
    }

    /**
     * 登录用户的会话。
     */
    public function forUser(string $userUid): static
    {
        return $this->state(AgentAccount::user($userUid)->columns());
    }

    /**
     * 某台设备上游客的会话。
     */
    public function forGuest(string $deviceId): static
    {
        return $this->state(AgentAccount::guest($deviceId)->columns());
    }
}
