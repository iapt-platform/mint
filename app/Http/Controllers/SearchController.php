<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookTitle;
use App\Models\FtsText;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\PaliText;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SearchResource;
use App\Http\Resources\SearchBookResource;
use Illuminate\Support\Facades\Log;
use App\Tools\Tools;


class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $searchChapters = [];
        $searchBooks = [];
        $searchBookId = [];
        $queryBookId = '';

        if($request->has('book')){
            $queryBookId = ' AND pcd_book_id = ' . (int)$request->get('book');
        }else if($request->has('tags')){
            //查询搜索范围
            $tags = explode(',',$request->get('tags'));
            $tagIds = Tag::whereIn('name',$tags)->select('id')->get();
            $paliTextIds = TagMap::where('table_name','pali_texts')->whereIn('tag_id',$tagIds)->select('anchor_id')->get();
            $paliPara=[];
            foreach ($paliTextIds as $key => $value) {
                # code...
                if(isset($paliPara[$value->anchor_id])){
                    $paliPara[$value->anchor_id]++;
                }else{
                    $paliPara[$value->anchor_id]=1;
                }
            }
            $paliId=[];
            foreach ($paliPara as $key => $value) {
                # code...
                if($value===count($tags)){
                    $paliId[] = $key;
                }
            }
            $para = PaliText::where('level',1)->whereIn('uid',$paliId)->get();
            Log::info($para);

            if(count($para)>0){
                foreach ($para as $key => $value) {
                    # code...
                    $book_id = BookTitle::where('book',$value['book'])->where('paragraph',$value['paragraph'])->value('id');
                    if(!empty($book_id)){
                        $searchBookId[] = $book_id;
                    }
                    $searchChapters[] = ['book'=>$value['book'],'paragraph'=>$value['paragraph']];
                }
                $queryBookId = ' AND pcd_book_id in ('.implode(',',$searchBookId).') ';
            }
        }

        $key = $request->get('key');
        $param = [];
        switch ($request->get('match','case')) {
            case 'complete':
                # code...
                $querySelect_rank = "
                ts_rank('{0.1, 0.2, 0.4, 1}',
                    full_text_search_weighted,
                    websearch_to_tsquery('pali', ?))
                AS rank, ";
                $querySelect_highlight = " ts_headline('pali', content,
                websearch_to_tsquery('pali', ?),
                'StartSel = ~~, StopSel = ~~,MaxWords=3500, MinWords=3500,HighlightAll=TRUE')
                AS highlight,";
                $queryWhere = " full_text_search_weighted @@ websearch_to_tsquery('pali', ?) ";
                $param = [$key,$key,$key];
                break;
            case 'case':
                # code...
                $querySelect_rank = "
                ts_rank('{0.1, 0.2, 0.4, 1}',
                    full_text_search_weighted,
                    websearch_to_tsquery('pali', ?))
                AS rank, ";
                $querySelect_highlight = " ts_headline('pali', content,
                websearch_to_tsquery('pali', ?),
                'StartSel = ~~, StopSel = ~~,MaxWords=3500, MinWords=3500,HighlightAll=TRUE')
                AS highlight,";
                $queryWhere = " full_text_search_weighted @@ websearch_to_tsquery('pali', ?) ";
                $param = [$key,$key,$key];
                break;
            case 'similar':
                # 形似，去掉变音符号

                $querySelect_rank = "
                    ts_rank('{0.1, 0.2, 0.4, 1}',
                        full_text_search_weighted_unaccent,
                        websearch_to_tsquery('pali_unaccent', ?))
                    AS rank, ";
                    $querySelect_highlight = " ts_headline('pali_unaccent', content,
                        websearch_to_tsquery('pali_unaccent', ?),
                        'StartSel = ~~, StopSel = ~~,MaxWords=3500, MinWords=3500,HighlightAll=TRUE')
                        AS highlight,";
                $queryWhere = " full_text_search_weighted_unaccent @@ websearch_to_tsquery('pali_unaccent', ?) ";
                $key = Tools::getWordEn($key);
                $param = [$key,$key,$key];
                break;
        }


        $querySelect_2 = "  book,paragraph,content ";



        $queryCount = "SELECT count(*) as co FROM fts_texts WHERE {$queryWhere} {$queryBookId};";
        $resultCount = DB::select($queryCount, [$key]);

        $limit = $request->get('limit',10);
        $offset = $request->get('offset',0);
        switch ( $request->get('orderby',"rank")) {
            case 'rank':
                $orderby = " ORDER BY rank DESC ";
                break;
            case 'paragraph':
                $orderby = " ORDER BY book,paragraph ";
                break;
            default:
                $orderby = "";
                break;
        };
        $query = "SELECT
            {$querySelect_rank}
            {$querySelect_highlight}
            {$querySelect_2}
            FROM fts_texts
            WHERE
                {$queryWhere}
                {$queryBookId}
                {$orderby}
            LIMIT ? OFFSET ? ;";
        $param[] = $limit;
        $param[] = $offset;

        $result = DB::select($query, $param);

        //待查询单词列表
        //$caseMan = new CaseMan();
        //$wordSpell = $caseMan->BaseToWord($key);

        return $this->ok(["rows"=>SearchResource::collection($result),"count"=>$resultCount[0]->co]);
    }

    public function book_list(Request $request){
        $searchChapters = [];
        $searchBooks = [];
        $searchBookId = [];
        $queryBookId = '';

        if($request->has('tags')){
            //查询搜索范围
            $tags = explode(',',$request->get('tags'));
            $tagIds = Tag::whereIn('name',$tags)->select('id')->get();
            $paliTextIds = TagMap::where('table_name','pali_texts')->whereIn('tag_id',$tagIds)->select('anchor_id')->get();
            $paliPara=[];
            foreach ($paliTextIds as $key => $value) {
                # code...
                if(isset($paliPara[$value->anchor_id])){
                    $paliPara[$value->anchor_id]++;
                }else{
                    $paliPara[$value->anchor_id]=1;
                }
            }
            $paliId=[];
            foreach ($paliPara as $key => $value) {
                # code...
                if($value===count($tags)){
                    $paliId[] = $key;
                }
            }
            $para = PaliText::where('level',1)->whereIn('uid',$paliId)->get();
            Log::info($para);

            if(count($para)>0){
                foreach ($para as $key => $value) {
                    # code...
                    $book_id = BookTitle::where('book',$value['book'])->where('paragraph',$value['paragraph'])->value('id');
                    if(!empty($book_id)){
                        $searchBookId[] = $book_id;
                    }
                    $searchChapters[] = ['book'=>$value['book'],'paragraph'=>$value['paragraph']];
                }
                $queryBookId = ' AND pcd_book_id in ('.implode(',',$searchBookId).') ';
            }
        }
        $key = $request->get('key');

        $queryWhere = "( full_text_search_weighted @@ websearch_to_tsquery('pali', ?) OR
        full_text_search_weighted_unaccent @@ websearch_to_tsquery('pali_unaccent', ?) )";

        $query = "SELECT pcd_book_id, count(*) as co FROM fts_texts WHERE {$queryWhere} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
        $result = DB::select($query, [$key,$key]);

        return $this->ok(["rows"=>SearchBookResource::collection($result),"count"=>count($result)]);
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
