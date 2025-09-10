<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\UpdateChatMessageRequest;
use App\Models\ChatMessage;
use App\Http\Resources\ChatMessageResource;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $query = ChatMessage::where('chat_id', $request->get('chat'));

        $total = $query->count();

        $messages = $query->orderBy('id')->paginate($request->get('limit', 500));

        return $this->ok([
            'data' => ChatMessageResource::collection($messages),
            'total' => $total
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreChatMessageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChatMessageRequest $request)
    {

        $messagesData = $request->validated()['messages'];
        $chatId = $request->validated()['chat_id'];

        $created = [];
        foreach ($messagesData as $key => $data) {
            $data['chat_id'] = $chatId;
            $data['uid'] = (string) Str::uuid();

            // 如果是新消息且没有指定session_id，创建新的session
            if (empty($data['session_id']) && empty($data['parent_id'])) {
                $data['session_id'] = (string) Str::uuid();
            }
            // 如果有parent_id但没有session_id，继承父消息的session_id
            elseif (empty($data['session_id']) && !empty($data['parent_id'])) {
                $parent = ChatMessage::where('uid', $data['parent_id'])->first();
                if ($parent) {
                    $data['session_id'] = $parent->session_id;
                }
            }

            $created[] = ChatMessage::create($data);
        }

        return $this->ok([
            'data' => ChatMessageResource::collection($created),
            'total' => count($created),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ChatMessage  $chatMessage
     * @return \Illuminate\Http\Response
     */
    public function show(ChatMessage $chatMessage)
    {
        return $this->ok(new ChatMessageResource($chatMessage));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateChatMessageRequest  $request
     * @param  \App\Models\ChatMessage  $chatMessage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChatMessageRequest $request, ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChatMessage  $chatMessage
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChatMessage $chatMessage)
    {
        //
    }
}
