<?php

namespace App\Http\Controllers;

use App\Models\NissayaEnding;
use App\Models\Relation;
use Illuminate\Http\Request;
use App\Http\Resources\NissayaEndingResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\App;

class NissayaEndingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = NissayaEnding::select(['id','ending','lang','relation','editor_id','updated_at']);
        if(($request->has('search'))){
            $table->where('ending', 'like', $request->get('search')."%");
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

		if($result){
			return $this->ok(["rows"=>NissayaEndingResource::collection($result),"count"=>$count]);
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断权限
        $validated = $request->validate([
            'ending' => 'required',
            'lang' => 'required',
            'relation' => 'required'
        ]);
        $new = new NissayaEnding;
        $new->ending = $validated['ending'];
        $new->lang = $validated['lang'];
        $new->relation = $validated['relation'];
        $new->editor_id = $user['user_uid'];
        $new->save();
        return $this->ok(new NissayaEndingResource($new));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function show(NissayaEnding $nissayaEnding)
    {
        //
        return $this->ok(new NissayaEndingResource($nissayaEnding));

    }

    public function nissaya_card(Request $request)
    {
        //
        App::setLocale($request->get('lang'));

        $myEnding = NissayaEnding::where('ending',$request->get('ending'))
                                ->groupBy('relation')
                                ->select('relation')->get();
        if(count($myEnding) === 0){
            return $this->ok("# no record\n".$request->get('ending'));
        }

        $relations = Relation::whereIn('name',$myEnding)->get();

        if(count($relations) === 0){
            return $this->ok("# no relation\n".$request->get('ending'));
        }
        $output = "# 缅文语尾\n\n";
        $output .= "|格位|含义|翻译建议|关系|关系|\n";
        $output .= "|-|-|-|-|-|\n";
        foreach ($relations as $key => $relation) {
            if(empty($relation->case)){
                $output .= "|-|-|-|-|{$relation->name}|\n";
                continue;
            }
            $cases = json_decode($relation->case);
            foreach ($cases as $key => $case) {
                # code...
                $output .= "|".__("grammar.".$case);
                $output .= "|";
                //本地语言用法
                $output .= "|";
                $localLangs = NissayaEnding::where('relation',$relation['name'])
                                    ->where('lang',$request->get('lang'))->get();
                foreach ($localLangs as $localLang) {
                    # code...
                    $output .= $localLang->ending.",";
                }
                $output .= "|".__("grammar.relations.{$relation['name']}.label");
                $output .= "|".strtoupper($relation['name']);
                $output .= "|\n";
            }
        }

        return $this->ok($output);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NissayaEnding $nissayaEnding)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //查询是否重复
        if(NissayaEnding::where('ending',$request->get('ending'))
                 ->where('lang',$request->get('lang'))
                 ->where('relation',$request->get('relation'))
                 ->exists()){
            return $this->error(__('validation.exists',['name']));
        }
        $nissayaEnding->ending = $request->get('ending');
        $nissayaEnding->lang = $request->get('lang');
        $nissayaEnding->relation = $request->get('relation');
        $nissayaEnding->editor_id = $user['user_uid'];
        $nissayaEnding->save();
        return $this->ok(new NissayaEndingResource($nissayaEnding));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NissayaEnding  $nissayaEnding
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,NissayaEnding $nissayaEnding)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //TODO 判断当前用户是否有权限
        $delete = 0;
        $delete = $nissayaEnding->delete();

        return $this->ok($delete);
    }
}
