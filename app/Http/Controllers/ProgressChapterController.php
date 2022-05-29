<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ProgressChapter;
use App\Models\Channel;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\PaliText;
use App\Models\View;
use Illuminate\Http\Request;

class ProgressChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        if($request->get('progress')){
            $minProgress = (float)$request->get('progress');
        }else{
            $minProgress = 0.8;
        }
        if($request->get('offset')){
            $offset = (int)$request->get('offset');
        }else{
            $offset = 0;
        }
        //

        $chapters=false;
        switch ($request->get('view')) {
			case 'studio':
                #查询该studio的channel
                $channels = Channel::where('owner_uid',$request->get('id'))->select('uid')->get();
                $aChannel = [];
                foreach ($channels as $channel) {
                    # code...
                    $aChannel[] = $channel->uid;
                }
                $chapters = ProgressChapter::select($selectCol)
                                        ->whereIn('progress_chapters.channel_id', $aChannel)
                                        ->leftJoin('pali_texts', function($join)
                                                {
                                                    $join->on('progress_chapters.book', '=', 'pali_texts.book');
                                                    $join->on('progress_chapters.para','=','pali_texts.paragraph');
                                                })
                                        ->where('progress','>',0.85)
                                        ->orderby('progress_chapters.created_at','desc')
                                        ->get();
                break;
            case 'tag':
                $tm = (new TagMap)->getTable();
                $pc =(new ProgressChapter)->getTable();
                $t = (new Tag)->getTable();            
                $query = "select t.name,count(*) from $tm  tm
                            join tags as t on tm.tag_id = t.id
                            join progress_chapters as pc on tm.anchor_id = pc.uid
                            where tm.table_name  = 'progress_chapters' and 
                            pc.progress > ?
                            group by t.name;";
                $chapters = DB::select($query, [$minProgress]);
                if($chapters){
                    $all_count = count($chapters);
                }else{
                    $all_count = 0;
                }
                break;
            case 'chapter-tag':
                $tm = (new TagMap)->getTable();
                $pc =(new ProgressChapter)->getTable();
                $tg = (new Tag)->getTable();     
                $pt = (new PaliText)->getTable();  
                if($request->get('tags') && $request->get('tags')!==''){
                    $tags = explode(',',$request->get('tags'));
                    foreach ($tags as $tag) {
                        # code...
                        if(!empty($tag)){
                            $tagNames[] = $tag;
                        }
                    }
                }
                
                $param[] = $minProgress;
                if(isset($tagNames)){
                    $where1 = " where co = ".count($tagNames);
                    $a = implode(",",array_fill(0, count($tagNames), '?')) ;
                    $in1 = "and t.name in ({$a})";
                    $param  = array_merge($param, $tagNames);
                }else{
                    $where1 = " ";
                    $in1 = " ";
                }
                $query = "
                    select tags.id,tags.name,co as count 
                        from (
                            select tm.tag_id,count(*) as co from (
                                select anchor_id as id from (
                                    select tm.anchor_id , count(*) as co
                                        from $tm as  tm
                                        left join $tg as t on tm.tag_id = t.id
                                        left join $pc as pc on tm.anchor_id = pc.uid
                                        where tm.table_name  = 'progress_chapters' and 
                                              pc.progress  > ? 
                                        $in1
                                        group by tm.anchor_id
                                ) T 
                                    $where1
                            ) CID 
                            left join $tm as tm on tm.anchor_id = CID.id 
                            group by tm.tag_id
                        ) tid
                        left join $tg on $tg.id = tid.tag_id
                        order by count desc
                    ";
                    if(isset($param)){
                        $chapters = DB::select($query,$param);
                    }else{
                        $chapters = DB::select($query);
                    }
                    $all_count = count($chapters);
                break;
            case 'lang':
                $chapters = ProgressChapter::select('lang')
                                            ->selectRaw('count(*) as count')
                                            ->where("progress",">",$minProgress)
                                            ->groupBy('lang')
                                            ->get();
                $all_count = count($chapters);
                break;
            case 'channel-type':
                break;
            case 'channel':
            /*
            总共有多少channel
            */
                $chapters = ProgressChapter::select('channel_id')
                                            ->selectRaw('count(*) as count')
                                            ->with(['channel' => function($query) {  //city对应上面province模型中定义的city方法名  闭包内是子查询
                                                return $query->select('*');
                                            }])
                                            ->where("progress",">",$minProgress)
                                            ->groupBy('channel_id')
                                            ->orderBy('count','desc')
                                            ->get();
                $all_count = count($chapters);
                break;
            case 'chapter':
                $tm = (new TagMap)->getTable();
                $pc =(new ProgressChapter)->getTable();
                $tg = (new Tag)->getTable();     
                $pt = (new PaliText)->getTable();  
                if($request->get('tags') && $request->get('tags')!==''){
                    $tags = explode(',',$request->get('tags'));
                    foreach ($tags as $tag) {
                        # code...
                        if(!empty($tag)){
                            $tagNames[] = $tag;
                        }
                    }
                }
                
                
                if(isset($tagNames)){
                    $where1 = " where co = ".count($tagNames);
                    $a = implode(",",array_fill(0, count($tagNames), '?')) ;
                    $in1 = "and t.name in ({$a})";
                    $param = $tagNames;
                }else{
                    $where1 = " ";
                    $in1 = " ";
                }
                $param[] = $minProgress;
                $param[] = $offset;
                $query = "
                select tpc.uid, tpc.book ,tpc.para,tpc.channel_id,tpc.title,pt.toc,pt.path,tpc.progress,tpc.summary,tpc.created_at,tpc.updated_at 
                    from (
                        select * from (
                            select anchor_id as cid from (
                                select tm.anchor_id , count(*) as co 
                                    from $tm as  tm
                                    left join $tg as t on tm.tag_id = t.id
                                    where tm.table_name  = 'progress_chapters'  
                                    $in1
                                    group by tm.anchor_id
                            ) T 
                                $where1 
                        ) CID 
                        left join $pc as pc on CID.cid = pc.uid 
                        where pc.progress > ?
                        order by created_at desc
                        limit 20 offset ?
                    ) tpc 
                    left join $pt as pt on tpc.book = pt.book and tpc.para = pt.paragraph;";
                $chapters = DB::select($query,$param);
                foreach ($chapters as $key => $value) {
                    # code...
                    $chapters[$key]->channel = Channel::where('uid',$value->channel_id)->select(['name','owner_uid'])->first();
                    $chapters[$key]->views = View::where("target_id",$value->uid)->count();
                    $chapters[$key]->tags = TagMap::where("anchor_id",$value->uid)
                                                ->leftJoin('tags','tag_maps.tag_id', '=', 'tags.id')
                                                ->select(['tags.id','tags.name','tags.description'])
                                                ->get();
                }
                $all_count = 10;
                break;
        }
        if($chapters){
            return $this->ok(["rows"=>$chapters,"count"=>$all_count]);
        }else{
            return $this->error("no data");
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
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function show(ProgressChapter $progressChapter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function edit(ProgressChapter $progressChapter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProgressChapter $progressChapter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProgressChapter  $progressChapter
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProgressChapter $progressChapter)
    {
        //
    }
}
