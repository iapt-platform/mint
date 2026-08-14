<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $query = Chat::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $total = $query->count();

        $chats = $query->orderBy('updated_at', 'desc')
            ->paginate($request->input('limit', 20));

        return $this->ok([
            'rows' => ChatResource::collection($chats),
            'total' => $total,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreChatRequest $request)
    {
        $chat = Chat::create($request->validated());

        return $this->ok(new ChatResource($chat));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Chat $chat)
    {
        return $this->ok(new ChatResource($chat));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateChatRequest $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    /**
     * 单个软删除
     */
    public function destroy(Chat $chat)
    {
        $chat->delete(); // 软删除

        return $this->ok('Chat deleted successfully.');
    }

    /**
     * 批量软删除
     */
    public function batchDelete(Request $request)
    {
        $chatIds = $request->input('uids', []); // 前端传入数组
        $count = Chat::batchSoftDelete($chatIds);

        return $this->ok([
            'message' => 'Chats soft deleted successfully.',
            'deleted_count' => $count,
        ]);
    }

    /**
     * 批量恢复
     */
    public function batchRestore(Request $request)
    {
        $chatIds = $request->input('uids', []); // 前端传入数组
        $count = Chat::batchRestore($chatIds);

        return $this->ok([
            'message' => 'Chats restored successfully.',
            'restored_count' => $count,
        ]);
    }
}
