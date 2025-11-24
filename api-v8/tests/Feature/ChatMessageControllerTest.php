<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatMessageControllerTest extends TestCase
{
    use  WithFaker;

    protected $chat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chat = Chat::factory()->create();
    }

    public function test_can_create_chat_message()
    {
        $data = [
            'chat_id' => $this->chat->uid,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $this->faker->sentence
                ]
            ]
        ];

        $response = $this->postJson("/api/v2/chat-messages", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $this->chat->uid,
            'role' => 'user',
            'content' => $data['messages'][0]['content']
        ]);
    }

    public function test_can_list_chat_messages()
    {
        ChatMessage::factory()->count(3)->create([
            'chat_id' => $this->chat->id
        ]);

        $response = $this->getJson("/api/v2/chat-messages?chat={$this->chat->id}");

        $response->assertStatus(200);
    }
}
