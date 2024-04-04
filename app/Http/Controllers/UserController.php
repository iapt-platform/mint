<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\UserInfo;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get("view")) {
            case 'key':
                $table = UserInfo::where('username','like','%'.$request->get("key").'%')
                                ->orWhere('nickname','like','%'.$request->get("key").'%');

                break;

            case 'all':
                $table = UserInfo::where('id','>',0);
                break;
        }
        if($request->has("search")){
            $table = $table->where('nickname', 'like', "%".$request->get("search")."%");
        }
        if($request->has("role")){
            $table = $table->whereJsonContains('role',$request->get('role'));
        }
        $count = $table->count();
        $table = $table->orderBy($request->get('order','username'),
                                 $request->get('dir','desc'));
        $table = $table->skip($request->get("offset",0))
                       ->take($request->get('limit',20));
        $result = $table->get();
        return $this->ok(['rows'=>UserResource::collection($result),'count'=>$count]);

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user = UserInfo::where('userid',$id)->first();
        return $this->ok(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = UserInfo::where('userid',$id)->first();
        if($request->has('roles')){
            $user->role = json_encode($request->get('roles'));
        }else{
            $user->nickname = $request->get('nickName');
            $user->avatar = $request->get('avatar');
            $user->email = $request->get('email');
        }
        $user->save();
        return $this->ok(new UserResource($user));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
