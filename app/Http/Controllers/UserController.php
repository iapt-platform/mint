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
        }
        $count = $table->count();
        $table = $table->orderBy($request->get('order','username'),$request->get('dir','desc'));
        $table = $table->skip($request->get("offset",0))
                       ->take($request->get('limit',20));
        $result = $table->get();
        if($result){
            return $this->ok(['rows'=>UserResource::collection($result),'count'=>$count]);
        }else{
            return $this->error();
        }
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
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
