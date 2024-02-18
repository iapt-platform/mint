<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentenceInfoController;
use App\Http\Controllers\WbwAnalysisController;
use App\Http\Controllers\PageIndexController;
use App\Http\Controllers\AssetsController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/app', '/app/pcdl/index.php');
Route::redirect('/app/pcdl', '/app/pcdl/index.php');

Route::get('/', [PageIndexController::class,'index']);

Route::get('/api/sentence/progress/image', [SentenceInfoController::class,'showprogress']);
Route::get('/api/sentence/progress/daily/image', [SentenceInfoController::class,'showprogressdaily']);
Route::get('/wbwanalyses', [WbwAnalysisController::class,'index']);
Route::get('/attachments/{bucket}/{name}',[AssetsController::class,'show']);

Route::get('/export/wbw', function (){
    return view('export_wbw',['sentences' => []]);
});

Route::get('/privacy/{file}', function ($file){
    $content = file_get_contents(base_path("/documents/mobile/privacy/{$file}.md"));
    return view('privacy',['content' => $content]);
});
Route::redirect('/privacy', '/privacy/index');

