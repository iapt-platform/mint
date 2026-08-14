<?php

namespace App\Http\Controllers;

use App\Models\PageNumber;
use App\Models\WbwTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchPageNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  PageNumber  $pageNumber
     * @return Response
     */
    public function show(string $number)
    {
        $pages = PageNumber::where('page', $number)
            ->select(['type', 'volume', 'page', 'book', 'paragraph', 'pcd_book_id'])
            ->get();
        $para = WbwTemplate::where('real', 'para'.$number)->select(['book', 'paragraph', 'pcd_book_id'])->get();
        foreach ($para as $key => $value) {
            // code...
            $pages[] = [
                'type' => 'para',
                'volume' => 0,
                'page' => $number,
                'book' => $value->book,
                'paragraph' => $value->paragraph,
                'pcd_book_id' => $value->pcd_book_id,
            ];
        }

        return $this->ok($pages);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, PageNumber $pageNumber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(PageNumber $pageNumber)
    {
        //
    }
}
