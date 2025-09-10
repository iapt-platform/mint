<?php

namespace Database\Factories;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChatFactory extends Factory
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
            'title' => $this->faker->sentence(3),
            'user_id' => 'ba5463f3-72d1-4410-858e-eadd10884713', // 或者 User::factory() 如果有用户系统
        ];
    }
}
