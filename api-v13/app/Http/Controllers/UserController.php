<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->input('view')) {
            case 'key':
                $table = UserInfo::where('username', 'like', '%'.$request->input('key').'%')
                    ->orWhere('nickname', 'like', '%'.$request->input('key').'%');

                break;

            case 'all':
                $table = UserInfo::where('id', '>', 0);
                break;
        }
        if ($request->has('search')) {
            $table = $table->where('nickname', 'like', '%'.$request->input('search').'%');
        }
        if ($request->has('role')) {
            $table = $table->whereJsonContains('role', $request->input('role'));
        }
        $count = $table->count();
        $table = $table->orderBy(
            $request->input('order', 'username'),
            $request->input('dir', 'desc')
        );
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 20));
        $result = $table->get();

        return $this->ok(['rows' => UserResource::collection($result), 'count' => $count]);
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
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
        $user = UserInfo::where('userid', $id)->first();

        return $this->ok(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  string  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = UserInfo::where('userid', $id)->first();
        if ($request->has('roles')) {
            $user->role = json_encode($request->input('roles'));
        } else {
            $user->nickname = $request->input('nickName');
            $user->avatar = $request->input('avatar');
            $user->email = $request->input('email');
        }
        $user->save();

        return $this->ok(new UserResource($user));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
