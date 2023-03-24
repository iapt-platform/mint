<?php

namespace App\Http\Controllers;

use App\Models\DhammaTerm;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\ShareApi;
use App\Tools\Tools;
use App\Http\Resources\TermResource;
use Illuminate\Support\Facades\App;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
		$indexCol = ['id','guid','word','meaning','other_meaning','note','tag','language','channal','owner','editor_id','created_at','updated_at'];

		switch ($request->get('view')) {
            case 'create-by-channel':
                # 新建术语时。根据术语所在channel 给出新建术语所需数据。如语言，备选意思等。
                #获取channel信息
                $currChannel = Channel::where('uid',$request->get('channel'))->first();
                if(!$currChannel){
                    return $this->error(__('auth.failed'));
                }
                #TODO 查询studio信息
                #获取同studio的channel列表
                $studioChannels = Channel::where('owner_uid',$currChannel->owner_uid)
                                        ->select(['name','uid'])
                                        ->get();
                #获取全网意思列表
                $meanings = DhammaTerm::where('word',$request->get('word'))
                                        ->where('language',$currChannel->lang)
                                        ->select(['meaning','other_meaning'])
                                        ->get();
                $meaningList=[];
                foreach ($meanings as $key => $value) {
                    # code...
                    $meaning1 = [$value->meaning];

                    if(!empty($value->other_meaning)){
                        $meaning2 = \explode(',',$value->other_meaning);
                        $meaning1 = array_merge($meaning1,$meaning2);
                    }
                    foreach ($meaning1 as $key => $value) {
                        # code...
                        if(isset($meaningList[$value])){
                            $meaningList[$value]++;
                        }else{
                            $meaningList[$value] = 1;
                        }
                    }
                }
                $meaningCount = [];
                foreach ($meaningList as $key => $value) {
                    # code...
                    $meaningCount[] = ['meaning'=>$key,'count'=>$value];
                }
                return $this->ok([
                    "word"=>$request->get('word'),
                    "meaningCount"=>$meaningCount,
                    "studioChannels"=>$studioChannels,
                    "language"=>$currChannel->lang,
                ]);
                break;
            case 'studio':
				# 获取 studio 内所有 term
                $search = $request->get('search');
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== StudioApi::getIdByName($request->get('name'))){
                    return $this->error(__('auth.failed'));
                }
                $table = DhammaTerm::select($indexCol)
                                   ->where('owner', $user["user_uid"]);
				break;
            case 'channel':
                # 获取 studio 内所有 term
                $search = $request->get('search');
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的 channel 的权限
                $channel = Channel::find($request->get('id'));
                if($user['user_uid'] !== $channel->owner_uid ){
                    //看是否为协作
                    $power = ShareApi::getResPower($user['user_uid'],$request->get('id'));
                    if($power === 0){
                        return $this->error(__('auth.failed'));
                    }
                }
                $table = DhammaTerm::select($indexCol)
                                    ->where('channal', $request->get('id'));
                break;
            case 'show':
                return $this->ok(DhammaTerm::find($request->get('id')));
                break;
			case 'user':
				# code...
                $userUid = $_COOKIE['user_uid'];
                $search = $request->get('search');
				$table = DhammaTerm::select($indexCol)
									->where('owner', $userUid);
				break;
			case 'word':
				$table = DhammaTerm::select($indexCol)
									->where('word', $request->get("word"));
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

		if($result){
			return $this->ok(["rows"=>TermResource::collection($result),"count"=>$count]);
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $validated = $request->validate([
            'word' => 'required',
            'meaning' => 'required',
            'language' => 'required'
        ]);
        #查询重复的
        /*
        重复判定：
        一个channel下面word+tag+language 唯一
        */
        $table = DhammaTerm::where('owner', $user["user_uid"])
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
            $term->id = app('snowflake')->id();
            $term->guid = Str::uuid();
            $term->word = $request->get("word");
            $term->word_en = Tools::getWordEn($request->get("word"));
            $term->meaning = $request->get("meaning");
            $term->other_meaning = $request->get("other_meaning");
            $term->note = $request->get("note");
            $term->tag = $request->get("tag");
            $term->channal = $request->get("channal");
            $term->language = $request->get("language");
            if($request->has("channal")){
                $channelInfo = ChannelApi::getById($request->get("channal"));
                if(!$channelInfo){
                    return $this->error("channel id failed");
                }else{
                    $term->owner = $channelInfo['studio_id'];
                }
            }else{
                $term->owner = StudioApi::getIdByName($request->get("studioName"));
            }
            $term->editor_id = $user["user_id"];
            $term->create_time = time()*1000;
            $term->modify_time = time()*1000;
            $term->save();
            return $this->ok($term);
        }else{
            return $this->error("word existed");
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request,$id)
    {
        //
		$indexCol = ['id','guid','word','meaning','other_meaning','tag','note','language','channal','created_at','updated_at'];

		$result  = DhammaTerm::select($indexCol)->where('guid', $id)->first();
		if($result){
			return $this->ok($result);
		}else{
			return $this->error("没有查询到数据");
		}

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $dhammaTerm = DhammaTerm::find($id);
        $dhammaTerm->word = $request->get("word");
        $dhammaTerm->word_en = Tools::getWordEn($request->get("word"));
        $dhammaTerm->meaning = $request->get("meaning");
        $dhammaTerm->other_meaning = $request->get("other_meaning");
        $dhammaTerm->note = $request->get("note");
        $dhammaTerm->tag = $request->get("tag");
        $dhammaTerm->channal = $request->get("channal");
        $dhammaTerm->language = $request->get("language");
        if($request->has("channal")){
            $channelInfo = ChannelApi::getById($request->get("channal"));
            if(!$channelInfo){
                return $this->error("channel id failed");
            }else{
                $dhammaTerm->owner = $channelInfo['studio_id'];
            }
        }else{
            $dhammaTerm->owner = StudioApi::getIdByName($request->get("studioName"));
        }
        $dhammaTerm->editor_id = $user["user_id"];
        $dhammaTerm->create_time = time()*1000;
        $dhammaTerm->modify_time = time()*1000;
        $dhammaTerm->save();
		return $this->ok($dhammaTerm);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DhammaTerm  $dhammaTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(DhammaTerm $dhammaTerm,Request $request)
    {
        /**
         * 一次删除多个单词
         */
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $count = 0;
        if($request->has("uuid")){
            //查看是否有删除权限
            foreach ($request->get("id") as $key => $uuid) {
                $term = DhammaTerm::find($uuid);
                if($term->owner !== $user['user_uid']){
                    if(!empty($term->channal)){
                        //看是否为协作
                        $power = ShareApi::getResPower($user['user_uid'],$term->channal);
                        if($power < 20){
                            continue;
                        }
                    }else{
                        continue;
                    }
                }
                $count += $term->delete();
            }
        }else{
            $arrId = json_decode($request->get("id"),true) ;
            foreach ($arrId as $key => $id) {
                # code...
                $result = DhammaTerm::where('id', $id)
                                ->where('owner', $user['user_uid'])
                                ->delete();
                if($result){
                    $count++;
                }
            }
        }

		return $this->ok($count);
    }

    public function export(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
//TODO 判断是否有导出权限
        switch ($request->get("view")) {
            case 'channel':
                # code...
                $rows = DhammaTerm::where('channal',$request->get("id"))->cursor();
                break;
            case 'studio':
                # code...
                $rows = DhammaTerm::where('channal',$request->get("name"))->cursor();
                break;
            default:
                $this->error('no view');
                break;
        }

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'id');
        $activeWorksheet->setCellValue('B1', 'word');
        $activeWorksheet->setCellValue('C1', 'meaning');
        $activeWorksheet->setCellValue('D1', 'other_meaning');
        $activeWorksheet->setCellValue('E1', 'note');
        $activeWorksheet->setCellValue('F1', 'tag');
        $activeWorksheet->setCellValue('G1', 'language');
        $activeWorksheet->setCellValue('H1', 'channel_id');

        $currLine = 2;
        foreach ($rows as $key => $row) {
            # code...
            $activeWorksheet->setCellValue("A{$currLine}", $row->guid);
            $activeWorksheet->setCellValue("B{$currLine}", $row->word);
            $activeWorksheet->setCellValue("C{$currLine}", $row->meaning);
            $activeWorksheet->setCellValue("D{$currLine}", $row->other_meaning);
            $activeWorksheet->setCellValue("E{$currLine}", $row->note);
            $activeWorksheet->setCellValue("F{$currLine}", $row->tag);
            $activeWorksheet->setCellValue("G{$currLine}", $row->language);
            $activeWorksheet->setCellValue("H{$currLine}", $row->channal);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        $fId = Str::uuid();
        $filename = storage_path("app/tmp/{$fId}");
        $writer->save($filename);
        Cache::put("download/tmp/{$fId}",file_get_contents($filename),300);
        unlink($filename);
        return $this->ok(['uuid'=>$fId,'filename'=>"term.xlsx",'type'=>"application/vnd.ms-excel"]);
    }

    public function import(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        $filename = $request->get('filename');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $currLine = 2;
        do {
            # code...
            $id = $activeWorksheet->getCell("A{$currLine}")->getValue();
            $word = $activeWorksheet->getCell("B{$currLine}")->getValue();
            $meaning = $activeWorksheet->getCell("C{$currLine}")->getValue();
            $other_meaning = $activeWorksheet->getCell("D{$currLine}")->getValue();
            $note = $activeWorksheet->getCell("E{$currLine}")->getValue();
            $tag = $activeWorksheet->getCell("F{$currLine}")->getValue();
            $language = $activeWorksheet->getCell("G{$currLine}")->getValue();
            $query = ['word'=>$word,'tag'=>$tag];
            $channelId = null;
            switch ($request->get('view')) {
                case 'channel':
                    # code...
                    $query['channal'] = $request->get('id');
                    $channel = ChannelApi::getById($request->get('id'));
                    $owner_id = $channel['studio_id'];
                    $lang = $channel['lang'];
                    $channelId = $request->get('id');
                    break;
                case 'studio':
                    # code...
                    break;
                default:
                    # code...
                    break;
            }

            if(!empty($word)){
                $row = DhammaTerm::where($query)->first();
                if(!$row){
                    $row = new DhammaTerm();
                    $row->id = app('snowflake')->id();
                    $row->guid = Str::uuid();
                    $row->word = $word;
                    $row->create_time = time()*1000;
                }
                $row->word_en = Tools::getWordEn($word);
                $row->meaning = $meaning;
                $row->other_meaning = $other_meaning;
                $row->note = $note;
                $row->tag = $tag;
                $row->language = $lang;
                $row->channal = $channelId;
                $row->owner = $owner_id;
                $row->editor_id = $user['user_id'];
                $row->owner = $owner_id;
                $row->modify_time = time()*1000;
                $row->save();
            }else{
                break;
            }
            $currLine++;
        } while (!empty($word));
        return $this->ok($currLine-2);
    }
}
