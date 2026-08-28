<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecentResource;
use App\Models\Recent;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class RecentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->view) {
            case 'user':
                // recents.user_uid 是 uuid 列，非法值直接拒绝，避免 Postgres 报 22P02。
                $userUid = $request->input('id');
                if (! Str::isUuid($userUid)) {
                    return $this->error('invalid id', [], 422);
                }
                $table = Recent::where('user_uid', $userUid);
                break;
            default:
                return $this->error('known view');
                break;
        }
        if ($request->has('type')) {
            $table->where('type', $request->input('type'));
        }
        $table->orderBy($request->input('order', 'updated_at'), $request->input('dir', 'desc'));
        $count = $table->count();
        $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        return $this->ok(['rows' => RecentResource::collection($result), 'count' => $count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }

        $validated = $request->validate([
            'type' => 'required',
            'article_id' => 'required',
        ]);

        $row = Recent::firstOrNew([
            'type' => $request->input('type'),
            'article_id' => $request->input('article_id'),
            'user_uid' => $user['user_uid'],
        ], [
            'id' => Str::uuid(),
        ]);
        $row->param = $request->input('param', null);
        $row->save();

        return $this->ok(new RecentResource($row));
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Recent $recent)
    {
        //
        return $this->ok(new RecentResource($recent));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Recent $recent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Recent $recent)
    {
        //
    }
}
