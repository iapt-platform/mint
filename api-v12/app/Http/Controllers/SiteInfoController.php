<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use App\Services\AIModelService;


class SiteInfoController extends Controller
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
     * @param  string  $language
     * @return \Illuminate\Http\Response
     */
    public function show($language)
    {
        if (!in_array($language, ['en', 'zh-Hans', 'zh-Hant'])) {
            App::setLocale('en');
        } else {
            App::setLocale($language);
        }
        $model = app(AIModelService::class);
        $response = [
            'logo' => __("site.logo"),
            'title' => __('site.title'),
            'subhead' => __('site.subhead'),
            'keywords' => __('site.keywords'),
            'description' => __('site.description'),
            'copyright' => __('site.copyright'),
            'author' => [
                'name' => __('site.author.name'),
                'email' => __('site.author.email'),
            ],
            'settings' => [
                'models' => $model->getSysModels(),
            ]
        ];
        return response()->json(
            $response,
            200,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8'
            ],
            JSON_UNESCAPED_UNICODE
        );
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
