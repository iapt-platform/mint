<?php

namespace Database\Factories;

use App\Models\AiModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AiModel>
 */
class AiModelFactory extends Factory
{
    protected $model = AiModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'name' => fake()->unique()->slug(2),
            // real_name 是模型的登录标识，表上有 unique 约束
            'real_name' => (string) Str::uuid(),
            'description' => fake()->sentence(),
            'url' => 'https://api.example.com',
            'model' => 'gpt-4',
            'key' => 'sk-'.fake()->uuid(),
            'system_prompt' => 'you are a helpful assistant',
            'privacy' => 'private',
            'owner_id' => (string) Str::uuid(),
            'editor_id' => (string) Str::uuid(),
        ];
    }

    /**
     * AiModel 没有声明 $fillable（默认 guarded = ['*']），构造器里 fill() 会抛
     * MassAssignmentException。这里绕开批量赋值保护，而不是为了测试去放开生产模型的写入面。
     */
    public function newModel(array $attributes = [])
    {
        $model = $this->modelName();

        return (new $model)->forceFill($attributes);
    }

    /**
     * 归属于指定 studio（个人 studio 即用户 uid）。
     */
    public function ownedBy(string $ownerId): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $ownerId,
            'editor_id' => $ownerId,
        ]);
    }
}
