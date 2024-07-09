<?php

namespace App\Http\Controllers;

use App\Models\Recent;
use Illuminate\Http\Request;
use App\Http\Resources\RecentResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Str;

class RecentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->view) {
            case 'user':
                $table = Recent::where('user_uid',$request->get('id'));
                break;
            default:
                return $this->error('known view');
                break;
        }

        $table->orderBy($request->get('order','updated_at'),$request->get('dir','desc'));
        $count = $table->count();
        $table->skip($request->get("offset",0))
              ->take($request->get('limit',1000));

        $result = $table->get();
		return $this->ok(["rows"=>RecentResource::collection($result),"count"=>$count]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[],401);
        }

        $validated = $request->validate([
            'type' => 'required',
            'article_id' => 'required',
        ]);

        $row = Recent::firstOrNew([
            "type"=>$request->get("type"),
            "article_id"=>$request->get("article_id"),
            "user_uid"=>$user['user_uid'],
        ],[
            "id"=>Str::uuid(),
        ]);
        $row->param = $request->get("param",null);
        $row->save();
        return $this->ok(new RecentResource($row));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Recent  $recent
     * @return \Illuminate\Http\Response
     */
    public function show(Recent $recent)
    {
        //
        return $this->ok(new RecentResource($recent));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recent  $recent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Recent $recent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recent  $recent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recent $recent)
    {
        //
    }
}
