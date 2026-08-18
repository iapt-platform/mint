<?php

namespace App\Http\Controllers;

use App\Services\AIModelService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class SiteInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
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
     * @param  string  $language
     * @return Response
     */
    public function show($language)
    {
        if (! in_array($language, ['en', 'zh-Hans', 'zh-Hant'])) {
            App::setLocale('en');
        } else {
            App::setLocale($language);
        }
        $model = app(AIModelService::class);
        $response = [
            'logo' => __('site.logo'),
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
            ],
        ];

        return response()->json(
            $response,
            200,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8',
            ],
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
