<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\ProgressChapter;
use Illuminate\Http\Request;

class TagsInChapterCountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        switch ($request->get('view')) {
            case "chapter":
                $progress = $request->get('progress',0.8);
                $lang = $request->get('lang');
                $channelType = $request->get('type','translation');

                $tm = (new TagMap)->getTable();
                $pc =(new ProgressChapter)->getTable();
                $tg = (new Tag)->getTable();

                //标签过滤
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
                if(Str::isUuid($request->get('channel'))){
                    $channel = "and channel_id = '".$request->get('channel')."' ";
                }else{
                    $channel = "";
                }
                //完成度过滤
                $param[] = $progress;

                //语言过滤
                if(!empty($request->get('lang'))){
                    $whereLang = " and pc.lang = ? ";
                    $param[] = $request->get('lang');
                }else{
                    $whereLang = "   ";
                }
                //channel type过滤
				if($request->has('channel_type') && !empty($request->get('channel_type'))){
					$channel_type = "and ch.type = ? ";
					$param[] = $request->get('channel_type');
				}else{
					$channel_type = "";
				}

                $param_count = $param;

                $query = "
                select TID.tag_id as id,name, TID.count from(
                    select tm2.tag_id, count(*)      from(
						select pcd.uid as pc_uid
							from (
								select uid, book,para,lang,progress,channel_id,title,summary ,created_at ,updated_at
									from (
										select anchor_id as cid
											from (
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
								$channel  $whereLang
							) pcd
						left join channels as ch on pcd.channel_id = ch.uid
						where ch.status >= 30 $channel_type
                    ) CUID
                    left join tag_maps tm2 on CUID.pc_uid = tm2.anchor_id
				group by tm2.tag_id
				) TID
				left join tags t2 on t2.id = TID.tag_id
				order by count desc";
                $result = DB::select($query,$param);
                return $this->ok(['rows'=>$result,'count'=>count($result)]);
                break;
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
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tag $tag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        //
    }
}
