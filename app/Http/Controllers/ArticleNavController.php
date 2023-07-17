<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ArticleNavResource;
use App\Models\PaliText;

class ArticleNavController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('type')) {
            case 'chapter':
                $para = explode('-',$request->get('id'));
                $prev = PaliText::where('book',$para[0])
                                ->where('paragraph','<',$para[1])
                                ->where('level','<',8)
                                ->orderBy('paragraph','desc')
                                ->first();
                $next = PaliText::where('book',$para[0])
                                ->where('paragraph','>',$para[1])
                                ->where('level','<',8)
                                ->orderBy('paragraph','asc')
                                ->first();
                if($prev){
                    $nav['prev']['id'] = $prev->book . '-' . $prev->paragraph;
                    $nav['prev']['title'] = $prev->toc;
                    $nav['prev']['subtitle'] = $prev->toc;
                }
                if($next){
                    $nav['next']['id'] = $next->book . '-' . $next->paragraph;
                    $nav['next']['title'] = $next->toc;
                    $nav['next']['subtitle'] = $next->toc;
                }
                break;
            case 'para':
                $para = explode('-',$request->get('id'));
                $prev = PaliText::where('book',$para[0])
                                ->where('paragraph','<',$para[1])
                                ->orderBy('paragraph','desc')
                                ->first();
                $next = PaliText::where('book',$para[0])
                                ->where('paragraph','>',$para[1])
                                ->orderBy('paragraph','asc')
                                ->first();
                if($prev){
                    $nav['prev']['id'] = $prev->book . '-' . $prev->paragraph;
                    $nav['prev']['title'] = $prev->text;
                    $nav['prev']['subtitle'] = $prev->text;
                }
                if($next){
                    $nav['next']['id'] = $next->book . '-' . $next->paragraph;
                    $nav['next']['title'] = $next->text;
                    $nav['next']['subtitle'] = $next->text;
                }
                break;
            default:
                return $this->error('type?');
                break;
        }
        return $this->ok(new ArticleNavResource($nav));

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
