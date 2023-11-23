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
     * @param  string  $pageNumber
     * @return \Illuminate\Http\Response
     */
    public function show(string $pageNumber)
    {
        //1-M-1-37
        $id = explode('-',$pageNumber);
        if(count($id) !== 4){
            return $this->error('参数错误。参数应为4 实际得到'.count($id),400,400);
        }
        $pageCurr = PageNumber::where('book',$id[0])
                            ->where('type',$id[1])
                            ->where('volume',$id[2])
                            ->where('page',$id[3])
                            ->first();
        $pagePrev = PageNumber::where('book',$id[0])
                            ->where('type',$id[1])
                            ->where('volume',$id[2])
                            ->where('page',(int)$id[3]-1)
                            ->first();
        $pageNext = PageNumber::where('book',$id[0])
                            ->where('type',$id[1])
                            ->where('volume',$id[2])
                            ->where('page',(int)$id[3]+1)
                            ->first();
        return $this->ok([
            'curr'=>$pageCurr? new NavPageResource($pageCurr):null,
            'prev'=>$pagePrev? new NavPageResource($pagePrev):null,
            'next'=>$pageNext? new NavPageResource($pageNext):null,
        ]);
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
