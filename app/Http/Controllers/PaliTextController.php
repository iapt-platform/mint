<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PaliText;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Http\Request;

class PaliTextController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case 'chapter-tag':
                $tm = (new TagMap)->getTable();
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
                    $param  = $tagNames;
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
                                        left join $pt as pc on tm.anchor_id = pc.uid
                                        where tm.table_name  = 'pali_texts' 
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

            case 'chapter':
                $tm = (new TagMap)->getTable();
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
                    $where2 = "where level < 3";
                }else{
                    $where1 = " ";
                    $in1 = " ";
                    $where2 = "where level = 1";
                }
                $query = "
                        select uid as id,book,paragraph,level,toc as title,chapter_strlen,parent,path from (
                            select anchor_id as cid from (
                                select tm.anchor_id , count(*) as co 
                                    from $tm as  tm
                                    left join $tg as t on tm.tag_id = t.id
                                    where tm.table_name  = 'pali_texts'
                                    $in1
                                    group by tm.anchor_id
                            ) T 
                                $where1 
                        ) CID 
                        left join $pt as pt on CID.cid = pt.uid 
                        $where2
                        order by book,paragraph";
                        
                    if(isset($param)){
                        $chapters = DB::select($query,$param);
                    }else{
                        $chapters = DB::select($query);
                    }

                $all_count = count($chapters);
                break;
        }
        if($chapters){
            return $this->ok(["rows"=>$chapters,"count"=>$all_count]);
        }else{
            return $this->error("no data");
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
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function show(PaliText $paliText)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaliText $paliText)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaliText  $paliText
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaliText $paliText)
    {
        //
    }
}
