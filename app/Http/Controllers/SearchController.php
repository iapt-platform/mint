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
use App\Http\Resources\SearchTitleResource;
use App\Http\Resources\SearchBookResource;
use Illuminate\Support\Facades\Log;
use App\Tools\Tools;
use App\Models\WbwTemplate;
use App\Models\PageNumber;
use App\Tools\PaliSearch;
use Illuminate\Support\Facades\App;


class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        switch ($request->get('view','pali')) {
            case 'pali':
                $pageHead = ['M','P','T','V','O'];
                $key = $request->get('key');
                if(substr($key,0,4) === 'para' || in_array(substr($key,0,1),$pageHead)){
                    return $this->page($request);
                }else{
                    return $this->pali_rpc($request);
                }
                break;
            case 'page':
                return $this->page($request);
                break;
            case 'title':
                $key = strtolower($request->get('key'));
                $table = PaliText::where('level','<',8)
                                 ->where(function ($query) use($key){
                                     $query->where('title_en','like',"%{$key}%")
                                         ->orWhere('title','like',"%{$key}%");
                                 });
                Log::info($table->toSql());
                if($request->has('tags')){
                    //查询搜索范围
                    $tagItems = explode(';',$request->get('tags'));
                    $bookId = [];
                    foreach ($tagItems as $tagItem) {
                        # code...
                        $bookId = array_merge($bookId,$this->getBookIdByTags(explode(',',$tagItem)));
                    }
                    $table = $table->whereIn('pcd_book_id',$bookId);
                }
                $count = $table->count();
                $table = $table->orderBy($request->get('orderby','book'),$request->get('dir','asc'));
                $table = $table->skip($request->get("offset",0))
                               ->take($request->get('limit',10));
                $result = $table->get();
                return $this->ok(["rows"=>SearchTitleResource::collection($result),"count"=>$count]);
                break;
            default:
                # code...
                break;
        }
    }
    public function pali(Request $request)
    {
        //
        $bookId = [];
        if($request->has('book')){
            $bookId = [(int)$request->get('book')];
        }else if($request->has('tags')){
            //查询搜索范围
            //查询搜索范围
            $tagItems = explode(';',$request->get('tags'));

            foreach ($tagItems as $tagItem) {
                $bookId = array_merge($bookId,$this->getBookIdByTags(explode(',',$tagItem)));
            }
        }

        $searchChapters = [];
        $searchBooks = [];
        $searchBookId = [];
        $queryBookId = '';
        if(count($bookId) > 0){
            $queryBookId = ' AND pcd_book_id in ('.implode(',',$bookId).') ';
        }
        $key = explode(';',$request->get('key')) ;
        $param = [];
        $countParam = [];
        switch ($request->get('match','case')) {
            case 'complete':
            case 'case':
                # code...
                $querySelect_rank_base = " ts_rank('{0.1, 1, 0.3, 0.2}',
                                                full_text_search_weighted,
                                                websearch_to_tsquery('pali', ?)) ";
                $querySelect_rank_head = implode('+', array_fill(0, count($key), $querySelect_rank_base));
                $param = array_merge($param,$key);
                $querySelect_rank = " {$querySelect_rank_head} AS rank, ";
                $querySelect_highlight = " ts_headline('pali', content,
                                            websearch_to_tsquery('pali', ?),
                                            'StartSel = ~~, StopSel = ~~,MaxWords=3500, MinWords=3500,HighlightAll=TRUE')
                                            AS highlight,";
                array_push($param,implode(' ',$key));
                break;
            case 'similar':
                # 形似，去掉变音符号
                $key = Tools::getWordEn($key[0]);
                $querySelect_rank = "
                    ts_rank('{0.1, 1, 0.3, 0.2}',
                        full_text_search_weighted_unaccent,
                        websearch_to_tsquery('pali_unaccent', ?))
                    AS rank, ";
                    $param[] = $key;
                $querySelect_highlight = " ts_headline('pali_unaccent', content,
                        websearch_to_tsquery('pali_unaccent', ?),
                        'StartSel = ~~, StopSel = ~~,MaxWords=3500, MinWords=3500,HighlightAll=TRUE')
                        AS highlight,";
                $param[] = $key;
                break;
        }
        $_queryWhere = $this->getQueryWhere($request->get('key'),$request->get('match','case'));
        $queryWhere = $_queryWhere['query'];
        $param = array_merge($param,$_queryWhere['param']);

        $querySelect_2 = "  book,paragraph,content ";

        $queryCount = "SELECT count(*) as co FROM fts_texts WHERE {$queryWhere} {$queryBookId};";
        $resultCount = DB::select($queryCount, $_queryWhere['param']);

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

        return $this->ok(["rows"=>SearchResource::collection($result),"count"=>$resultCount[0]->co]);
    }
    public function pali_rpc(Request $request)
    {
        //
        $bookId = [];
        if($request->has('book')){
            $bookId = [(int)$request->get('book')];
        }else if($request->has('tags')){
            //查询搜索范围
            //查询搜索范围
            $tagItems = explode(';',$request->get('tags'));

            foreach ($tagItems as $tagItem) {
                $bookId = array_merge($bookId,$this->getBookIdByTags(explode(',',$tagItem)));
            }
        }

        $key = explode(';',$request->get('key')) ;
        $limit = $request->get('limit',10);
        $offset = $request->get('offset',0);
        $matchMode = $request->get('match','case');
        $result = PaliSearch::search($key,$bookId,$matchMode,$offset,$limit);
        return $this->ok(["rows"=>SearchResource::collection(collect($result['rows'])),"count"=>$result['total']]);
    }

    public function page(Request $request)
    {
        //
        $searchChapters = [];
        $searchBooks = [];
        $searchBookId = [];
        $queryBookId = '';
        $bookId = [];
        if($request->has('book')){
            $bookId[] = $request->get('book');
        }else if($request->has('tags')){
            //查询搜索范围
            //查询搜索范围
            $tagItems = explode(';',$request->get('tags'));
            foreach ($tagItems as $tagItem) {
                # code...
                $bookId = array_merge($bookId,$this->getBookIdByTags(explode(',',$tagItem)));
            }
        }

        $key = $request->get('key');
        $searchKey = '';
        $page = explode('.',$key);
        if(count($page)===2){
            $table = PageNumber::where('type',$request->get('type'))
                               ->where('volume',(int)$page[0])
                               ->where('page',(int)$page[1]);
        }else{
            if(is_numeric($key)){
                $table = PageNumber::where('type',$request->get('type'))->where('page',$key);
            }else{
                $table = PageNumber::where('type',$request->get('type'))->where('page',(int)$key);
            }
        }



        if(count($bookId)>0){
            $table = $table->whereIn('pcd_book_id',$bookId);
        }
        $count = $table->count();
        $table = $table->select(['book','paragraph']);
        $table->skip($request->get("offset",0))->take($request->get('limit',10));
        $result = $table->get();

        return $this->ok(["rows"=>SearchResource::collection($result),"count"=>$count]);
    }

    public function book_list(Request $request){
        $searchChapters = [];
        $searchBooks = [];
        $queryBookId = '';

        $bookId = [];
        if($request->has('tags')){
            //查询搜索范围
            $tagItems = explode(';',$request->get('tags'));

            foreach ($tagItems as $tagItem) {
                # code...
                $bookId = array_merge($bookId,$this->getBookIdByTags(explode(',',$tagItem)));
            }
            $queryBookId = ' AND pcd_book_id in ('.implode(',',$bookId).') ';
        }
        $key = $request->get('key');
        switch ($request->get('view','pali')) {
            case 'pali':
                # code...
                $pageHead = ['M','P','T','V','O'];
                if(substr($key,0,4) === 'para' || in_array(substr($key,0,1),$pageHead)){
                    $queryWhere = "type='.ctl.' AND word = ?";
                    $query = "SELECT pcd_book_id, count(*) as co FROM wbw_templates WHERE {$queryWhere} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
                    $result = DB::select($query, [$key]);

                }else{

                    $rpc_result = PaliSearch::book_list(explode(';',$key),
                                                        $bookId,
                                                        $request->get('match','case'));
                    $result = collect($rpc_result['rows']);
                    /*
                        $queryWhere = $this->getQueryWhere($key,$request->get('match','case'));
                        $query = "SELECT pcd_book_id, count(*) as co FROM fts_texts WHERE {$queryWhere['query']} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
                        $result = DB::select($query, $queryWhere['param']);
                    */
                }
                break;
            case 'page':
                $type = $request->get('type','P');
                $word = "{$type}%0{$key}";
                $queryWhere = "type='.ctl.' AND word like ?";
                $query = "SELECT pcd_book_id, count(*) as co FROM wbw_templates WHERE {$queryWhere} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
                $result = DB::select($query, [$word]);
                break;
            case 'title':
                $keyLike = '%'.$key.'%';
                $queryWhere = "\"level\" < 8 and (\"title_en\"::text like ? or \"title\"::text like ?)";
                $query = "SELECT pcd_book_id, count(*) as co FROM pali_texts WHERE {$queryWhere} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
                $result = DB::select($query, [$keyLike,$keyLike]);
                break;
            default:
                # code...
                return $this->error('unknown view');
                break;
        }

        if($result){
            return $this->ok(["rows"=>SearchBookResource::collection($result),"count"=>count($result)]);
        }else{
            return $this->ok(["rows"=>[],"count"=>0]);
        }

    }

    private function getQueryWhere($key,$match){
        $key = explode(';',$key) ;
        $param = [];
        $queryWhere = '';
        switch ($match) {
            case 'complete':
            case 'case':
                # code...
                $queryWhereBase = " full_text_search_weighted @@ websearch_to_tsquery('pali', ?) ";
                $queryWhereBody = implode(' or ', array_fill(0, count($key), $queryWhereBase));
                $queryWhere = " ({$queryWhereBody}) ";
                $param = array_merge($param,$key);
                break;
            case 'similar':
                # 形似，去掉变音符号
                $queryWhere = " full_text_search_weighted_unaccent @@ websearch_to_tsquery('pali_unaccent', ?) ";
                $key = Tools::getWordEn($key[0]);
                $param = [$key];
                break;
        };
        return (['query'=>$queryWhere,'param'=>$param]);
    }

    public function getBookIdByTags($tags){
        $searchBookId = [];
        if(empty($tags)){
            return $searchBookId;
        }

        //查询搜索范围
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

        if(count($para)>0){
            foreach ($para as $key => $value) {
                # code...
                $book_id = BookTitle::where('book',$value['book'])
                                    ->where('paragraph',$value['paragraph'])
                                    ->value('sn');
                if(!empty($book_id)){
                    $searchBookId[] = $book_id;
                }
            }
        }
        return $searchBookId;

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
