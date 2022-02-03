<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;

class DhammaTermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result=false;
		$indexCol = ['id','guid','word','word_en','meaning','other_meaning','note','language','channal','updated_at'];

		switch ($request->get('view')) {
			case 'user':
				# code...
                $userUid = $_COOKIE['user_uid'];
                $search = $request->get('search');
				$table = DhammaTerm::select($indexCol)
									->where('owner', $userUid);
				if(!empty($search)){
					$table->where('word', 'like', $search."%")
                          ->whereOr('word_en', 'like', $search."%");
				}
				if(!empty($request->get('order')) && !empty($request->get('dir'))){
					$table->orderBy($request->get('order'),$request->get('dir'));
				}else{
					$table->orderBy('updated_at','desc');
				}
				$count = $table->count();
				if(!empty($request->get('limit'))){
					$offset = 0;
					if(!empty($request->get("offset"))){
						$offset = $request->get("offset");
					}
					$table->skip($offset)->take($request->get('limit'));
				}
				$result = $table->get();
				break;
			case 'word':
				$result = DhammaTerm::select($indexCol)
									->where('word', $request->get("word"))
									->orderBy('created_at','desc')
									->get();
				break;
			default:
				# code...
				break;
		}
		if($result){
			return $this->ok(["rows"=>$result,"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
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
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function show(DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DhammaTerm $dhammaTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm)
    {
        //
    }
}
