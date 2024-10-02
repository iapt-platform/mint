<?php

namespace App\Http\Controllers;

use App\Models\PageNumber;
use Illuminate\Http\Request;
use App\Http\Resources\NavPageResource;

class NavPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * 页码导航
     * 支持缅文版，PTS,等当前页，前一页，后一页，页面信息的获取
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
     * @param  string  $pageNumber
     * @return \Illuminate\Http\Response
     */
    public function show(string $pageNumber)
    {
        //M-99_100_101-1-37
        $id = explode('-',$pageNumber);
        if(count($id) !== 4){
            return $this->error('参数错误。参数应为4 实际得到'.count($id),400,400);
        }
        $books = explode('_',$id[1]);
        $pageCurr = PageNumber::whereIn('pcd_book_id',$books)
                            ->where('type',$id[0])
                            ->where('volume',$id[2])
                            ->where('page',$id[3])
                            ->first();
        $pagePrev = PageNumber::whereIn('pcd_book_id',$books)
                            ->where('type',$id[0])
                            ->where('volume',$id[2])
                            ->where('page',(int)$id[3]-1)
                            ->first();
        $pageNext = PageNumber::whereIn('pcd_book_id',$books)
                            ->where('type',$id[0])
                            ->where('volume',$id[2])
                            ->where('page',(int)$id[3]+1)
                            ->first();
        if($pageCurr){
            return $this->ok([
                'curr'=>$pageCurr? new NavPageResource($pageCurr):null,
                'prev'=>$pagePrev? new NavPageResource($pagePrev):null,
                'next'=>$pageNext? new NavPageResource($pageNext):null,
            ]);
        }else{
            return $this->error('page not found');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PageNumber  $pageNumber
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PageNumber $pageNumber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PageNumber  $pageNumber
     * @return \Illuminate\Http\Response
     */
    public function destroy(PageNumber $pageNumber)
    {
        //
    }
}
