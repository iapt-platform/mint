<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserDict;
use App\Models\DictInfo;
use Illuminate\Support\Facades\DB;

class DictStatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $items = array();
        $all = UserDict::count();
        $query = "SELECT count(*) from (SELECT word from user_dicts ud group by word) as t;";
		$allVocabulary = DB::select($query);
        $query = "SELECT count(*) from (SELECT parent from user_dicts ud group by parent) as t;";
		$allParent = DB::select($query);
        $items[] = ['key'=>'all','title'=>'all','count'=>$all,'vocabulary'=>$allVocabulary[0]->count,'parent'=>$allParent[0]->count];

        $dictName = ['robot_compound','system_regular','community_extract','community'];
        foreach ($dictName as $key => $name) {
            $dict = DictInfo::where('name',$name)->first();
            $all = UserDict::where('dict_id',$dict->id)->count();
            $query = "SELECT count(*) from (SELECT word from user_dicts ud where dict_id = ? group by word) as t;";
            $vocabulary = DB::select($query,[$dict->id]);
            $query = "SELECT count(*) from (SELECT parent from user_dicts ud where dict_id = ? group by parent) as t;";
            $parent = DB::select($query,[$dict->id]);
            $items[] = ['key'=>$dict->shortname,'title'=>$dict->shortname,'count'=>$all,'vocabulary'=>$vocabulary[0]->count,'parent'=>$parent[0]->count];
        }
        return $this->ok($items);
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
