<?php

namespace App\Http\Controllers;

use App\Models\Sentence;
use Illuminate\Http\Request;

class SentenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result=false;
		$indexCol = ['id','book_id','paragraph','word_start','word_end','content','channel_uid','updated_at'];

		switch ($request->get('view')) {
            case 'fulltext':
                if(isset($_COOKIE['user_uid'])){
                    $userUid = $_COOKIE['user_uid'];
                }
                $key = $request->get('key');
                if(empty($key)){
			        return $this->error("没有关键词");
                }
                $table = Sentence::select($indexCol)
								  ->where('content','like', '%'.$key.'%')
                                  ->where('editor_uid',$userUid);
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
			case 'user':
				# code...
                $userUid = $_COOKIE['user_uid'];
                $search = $request->get('search');
				$table = Sentence::select($indexCol)
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
				$result = Sentence::select($indexCol)
									->where('word', $request->get("word"))
									->orderBy('created_at','desc')
									->get();
				break;
            case 'hot-meaning':
                $key='term/hot_meaning';
                $value = Cache::get($key, function()use($request) {
                    $hotMeaning=[];
                    $words = Sentence::select('word')
                                ->where('language',$request->get("language"))
                                ->groupby('word')
                                ->get();
                    
                    foreach ($words as $key => $word) {
                        # code...
                        $result = Sentence::select(DB::raw('count(*) as word_count, meaning'))
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function show(Sentence $sentence)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function edit(Sentence $sentence)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sentence $sentence)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sentence  $sentence
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sentence $sentence)
    {
        //
    }
}
