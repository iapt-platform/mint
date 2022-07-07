<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            case 'show':
                return $this->ok(DhammaTerm::find($request->get('id')));
                break;
			case 'user':
				# code...
                $userUid = $_COOKIE['user_uid'];
                $search = $request->get('search');
				$table = DhammaTerm::select($indexCol)
									->where('owner', $userUid);
				if(!empty($search)){
					$table->where('word', 'like', $search."%")
                          ->orWhere('word_en', 'like', $search."%")
                          ->orWhere('meaning', 'like', "%".$search."%");
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
                $count = count($result);
				break;
            case 'hot-meaning':
                $key='term/hot_meaning';
                $value = Cache::get($key, function()use($request) {
                    $hotMeaning=[];
                    $words = DhammaTerm::select('word')
                                ->where('language',$request->get("language"))
                                ->groupby('word')
                                ->get();
                    
                    foreach ($words as $key => $word) {
                        # code...
                        $result = DhammaTerm::select(DB::raw('count(*) as word_count, meaning'))
                                ->where('language',$request->get("language"))
                                ->where('word',$word['word'])
                                ->groupby('meaning')
                                ->orderby('word_count','desc')
                                ->first();
                        if($result){
                            $hotMeaning[]=[
                                'word'=>$word['word'],
                                'meaning'=>$result['meaning'],
                                'language'=>$request->get("language"),
                                'owner'=>'',
                            ];
                        }
                    }
                    Cache::put($key, $hotMeaning, 3600);
                    return $hotMeaning;
                });
                return $this->ok(["rows"=>$value,"count"=>count($value)]);
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
                // validate
        // read more on validation at http://laravel.com/docs/validation
        $rules = array(
            'word' => 'required',
            'meaning' => 'required',
            'language' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);

        // process the login
        if ($validator->fails()) {
            return $this->error($validator);
        } else {
            #查询重复的
            /*
            重复判定：
            一个channel下面word+tag+language 唯一
            */
            $table = DhammaTerm::where('owner', $_COOKIE["user_uid"])
                    ->where('word',$request->get("word"))
                    ->where('tag',$request->get("tag"));
            if($request->get("channel")){
                $isDoesntExist = $table->where('channel',$request->get("channel"))
                      ->doesntExist();
            }else{
                $isDoesntExist = $table->where('language',$request->get("language"))
                    ->doesntExist();
            }
	
            if($isDoesntExist){
                #不存在插入数据
                $term = new DhammaTerm;
                $term->id=app('snowflake')->id();
                $term->guid=Str::uuid();
                $term->word=$request->get("word");
                $term->meaning=$request->get("meaning");
                $term->save();
                return $this->ok($data);

            }else{
                return $this->error("word existed");
            }
            // store
            /*
            $data = $request->all();
            $data['id'] = app('snowflake')->id();
            $data['guid'] = Str::uuid();
            DhammaTerm::create($data);
            */
            
        }
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
        return $dhammaTerm;
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
    public function destroy(DhammaTerm $dhammaTerm,Request $request)
    {
        //
        $arrId = json_decode($request->get("id"),true) ;
		$count = 0;
		foreach ($arrId as $key => $id) {
			# code...
			$result = DhammaTerm::where('id', $id)
							->where('owner', $_COOKIE["user_uid"])
							->delete();
            if($result){
                $count++;
            }
		}
		return $this->ok($count);
    }
}
