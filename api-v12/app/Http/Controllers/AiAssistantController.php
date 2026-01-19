<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use Illuminate\Http\Request;
use App\Http\Resources\AiAssistantResource;
use App\Http\Api\AuthApi;
use App\Http\Api\ShareApi;

use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthApi::current($request);
        if (!$user) {
            Log::error('notification auth failed {request}', ['request' => $request]);
            return $this->error(__('auth.failed'), 401, 401);
        }
        $resList = ShareApi::getResList($user['user_uid'], 8);
        $resId = [];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $table = AiModel::where('owner_id', $user['user_uid'])
            ->orWhere('privacy', 'public')
            ->orWhereIn('uid', $resId);
        if ($request->has('keyword')) {
            $table = $table->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->get('order', 'created_at'),
            $request->get('dir', 'asc')
        );

        $table = $table->skip($request->get("offset", 0))
            ->take($request->get('limit', 1000));

        $result = $table->get();

        return $this->ok(
            [
                "rows" => AiAssistantResource::collection(resource: $result),
                "count" => $count,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function show(AiModel $aiModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiModel $aiModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiModel $aiModel)
    {
        //
    }
}
