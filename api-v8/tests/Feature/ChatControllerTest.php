<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use  WithFaker;

    public function test_can_create_chat()
    {
        $data = [
            'title' => $this->faker->sentence,
            'user_id' => "ba5463f3-72d1-4410-858e-eadd10884713"
        ];

        $response = $this->postJson('/api/v2/chats', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'user_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('chats', [
            'title' => $data['title']
        ]);
    }

    public function test_can_list_chats()
    {
        Chat::factory()->count(1)->create();

        $response = $this->getJson('/api/v2/chats?user_id=ba5463f3-72d1-4410-858e-eadd10884713');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'rows' => [
                        '*' => [
                            'id',
                            'title',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_show_chat()
    {
        $chat = Chat::factory()->create();

        $response = $this->getJson("/api/v2/chats/{$chat->uid}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $chat->uid,
                    'title' => $chat->title
                ]
            ]);
    }



    public function test_can_delete_chat()
    {
        $chat = Chat::factory()->create();

        $response = $this->deleteJson("/api/chats/{$chat->uid}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('chats', [
            'id' => $chat->id
        ]);
    }
}
