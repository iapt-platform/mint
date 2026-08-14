<?php

namespace App\Http\Controllers;

use App\Http\Api\ShareApi;
use App\Http\Resources\AiAssistantResource;
use App\Models\AiModel;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AiAssistantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
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
            $table = $table->where('name', 'like', '%'.$request->input('keyword').'%');
        }
        $count = $table->count();

        $table = $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'asc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        return $this->ok(
            [
                'rows' => AiAssistantResource::collection(resource: $result),
                'count' => $count,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(AiModel $aiModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, AiModel $aiModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(AiModel $aiModel)
    {
        //
    }
}
