<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WbwTemplate;
use App\Http\Resources\SearchPaliWbwResource;
use App\Http\Resources\SearchBookResource;

class SearchPaliWbwController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //获取书的范围
        $bookId = [];
        $search = new SearchController;
        if($request->has('book')){
            foreach (explode(',',$request->get('book')) as $key => $id) {
                $bookId[] = (int)$id;
            }
        }else if($request->has('tags')){
            //查询搜索范围
            //查询搜索范围
            $tagItems = explode(';',$request->get('tags'));

            foreach ($tagItems as $tagItem) {
                $bookId = array_merge($bookId,$search->getBookIdByTags(explode(',',$tagItem)));
            }
        }

        $keyWords = explode(',',$request->get('key'));
        $table = WbwTemplate::whereIn('real',$keyWords)
                            ->groupBy(['book','paragraph'])
                            ->selectRaw('book,paragraph,sum(weight) as rank');
        $whereBold = '';
        if($request->get('bold')==='on'){
            $table = $table->where('style','bld');
            $whereBold = " and style='bld'";
        }else if($request->get('bold')==='off'){
            $table = $table->where('style','<>','bld');
            $whereBold = " and style <> 'bld'";
        }
        $placeholderWord = implode(",",array_fill(0, count($keyWords), '?')) ;
        $whereWord = "real in ({$placeholderWord})";
        $whereBookId = '';
        if(count($bookId)>0){
            $table =  $table->whereIn('pcd_book_id',$bookId);
            $placeholderBookId = implode(",",array_fill(0, count($bookId), '?')) ;
            $whereBookId = " and pcd_book_id in ({$placeholderBookId}) ";
        }
        $queryCount = "SELECT count(*) FROM ( SELECT book,paragraph FROM wbw_templates WHERE $whereWord $whereBookId $whereBold  GROUP BY book,paragraph) T;";
        $count = DB::select($queryCount,array_merge($keyWords,$bookId));

        $table =  $table->orderBy('rank','desc');
        $table =  $table->skip($request->get("offset",0))
                        ->take($request->get('limit',10));

        $result = $table->get();
        return $this->ok([
            "rows"=>SearchPaliWbwResource::collection($result),
            "count"=>$count[0]->count,
            ]);
    }

    public function book_list(Request $request){
        //获取书的范围
        $bookId = [];
        $search = new SearchController;
        if($request->has('tags')){
            //查询搜索范围
            //查询搜索范围
            $tagItems = explode(';',$request->get('tags'));

            foreach ($tagItems as $tagItem) {
                $bookId = array_merge($bookId,$search->getBookIdByTags(explode(',',$tagItem)));
            }
        }
        $keyWords = explode(',',$request->get('key'));
        $table = WbwTemplate::whereIn('real',$keyWords);

        if(count($bookId)>0){
            $table = $table->whereIn('pcd_book_id',$bookId);
        }
        $table = $table->groupBy('pcd_book_id')
                       ->selectRaw('pcd_book_id,count(*) as co')
                       ->orderBy('co','desc');
        $result = $table->get();
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
        return $this->index($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WbwTemplate  $wbwTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(WbwTemplate $wbwTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WbwTemplate  $wbwTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WbwTemplate $wbwTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WbwTemplate  $wbwTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(WbwTemplate $wbwTemplate)
    {
        //
    }
}
