<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class OfflineIndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $key = '/offline/index';

        if (!RedisClusters::has($key)) {
            return [];
        }
        $fileInfo = RedisClusters::get($key);
        $output = [];
        foreach ($fileInfo as $key => $file) {
            if ($request->has('file')) {
                if ($file['id'] !== $request->get('file')) {
                    continue;
                }
            }
            $zipFile = $file['filename'];
            $bucket = config('mint.attachments.bucket_name.temporary');
            $tmpFile =  $bucket . '/' . $zipFile;
            $url = array();
            foreach (config('mint.server.cdn_urls') as $key => $cdn) {
                $url[] = [
                    'link' => $cdn . '/' . $zipFile,
                    'hostname' => 'cdn-' . $key,
                ];
            }
            if (App::environment('local')) {
                $s3Link = Storage::url($tmpFile);
            } else {
                try {
                    $s3Link = Storage::temporaryUrl($tmpFile, now()->addDays(2));
                } catch (\Exception $e) {
                    Log::error('offline-index {Exception}', ['exception' => $e]);
                    continue;
                }
            }
            //Log::info('offline-index: link=' . $s3Link);
            $url[] = [
                'link' => $s3Link,
                'hostname' => 'Amazon cloud storage(Hongkong)',
            ];
            $file['url'] = $url;
            Log::debug('offline-index: file info=', ['data' => $file]);
            $output[] = $file;
        }
        return response()->json(
            $output,
            200,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8'
            ],
            JSON_UNESCAPED_UNICODE
        );
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
     * @param  string  $filename
     * @return \Illuminate\Http\Response
     */
    public function show($filename) {}

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
