<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WbwTemplateController;
use App\Http\Controllers\DhammaTermController;
use App\Http\Controllers\SentenceController;
use App\Http\Controllers\ProgressChapterController;
use App\Http\Controllers\SentenceInfoController;
use App\Http\Controllers\SentPrController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\SentHistoryController;
use App\Http\Controllers\PaliTextController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\UserDictController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v2'],function(){
	Route::apiResource('wbw_templates',WbwTemplateController::class);
	Route::apiResource('terms',DhammaTermController::class);
	Route::apiResource('sentence',SentenceController::class);
	Route::apiResource('sentpr',SentPrController::class);
	Route::apiResource('progress',ProgressChapterController::class);
	Route::apiResource('tag',TagController::class);
	Route::apiResource('view',ViewController::class);

    Route::delete('like', [LikeController::class, 'delete']);
    Route::apiResource('like',LikeController::class);
    Route::apiResource('sent_history',SentHistoryController::class);
    Route::apiResource('palitext',PaliTextController::class);
    Route::apiResource('channel',ChannelController::class);
    Route::delete('userdict', [UserDictController::class, 'delete']);
    Route::apiResource('userdict',UserDictController::class);

});
