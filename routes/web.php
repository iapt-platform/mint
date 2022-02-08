<?php

use Illuminate\Support\Facades\Route;
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

Route::redirect('/', '/app/pcdl/index.php');
Route::redirect('/app', '/app/pcdl/index.php');
Route::redirect('/app/pcdl', '/app/pcdl/index.php');

Route::get('/user/{id}', function ($id) {
    return 'User '.$id;
});

Route::get('/home/{name}', function ($name) {
    return view('home', ['name' => $name]);
});

Route::get('/wbwanalyses', [WbwAnalysisController::class,'index']);