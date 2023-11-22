<?php

namespace App\Http\Controllers;

use App\Models\PageNumber;
use App\Models\WbwTemplate;
use Illuminate\Http\Request;

class SearchPageNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
     * @param  \App\Models\PageNumber  $pageNumber
     * @return \Illuminate\Http\Response
     */
    public function show(string $number)
    {
        $pages = PageNumber::where('page',$number)
                        ->select(['type','volume','page','book','paragraph','pcd_book_id'])
                        ->get();
        $para = WbwTemplate::where('real','para'.$number)->select(['book','paragraph','pcd_book_id'])->get();
        foreach ($para as $key => $value) {
            # code...
            $pages[] = [
                'type'=>'para',
                'volume'=>0,
                'page'=>$number,
                'book'=>$value->book,
                'paragraph'=>$value->paragraph,
                'pcd_book_id'=>$value->pcd_book_id,
            ];
        }
        return $this->ok($pages);
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
