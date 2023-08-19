<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentenceInfoController;
use App\Http\Controllers\WbwAnalysisController;

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

Route::redirect('/', '/pcd/');
Route::redirect('/app', '/app/pcdl/index.php');
Route::redirect('/app/pcdl', '/app/pcdl/index.php');

Route::get('/api/sentence/progress/image', [SentenceInfoController::class,'showprogress']);
Route::get('/api/sentence/progress/daily/image', [SentenceInfoController::class,'showprogressdaily']);
Route::get('/wbwanalyses', [WbwAnalysisController::class,'index']);
Route::get('/export/wbw', function (){
    return view('export_wbw',['sentences' => []]);
});

