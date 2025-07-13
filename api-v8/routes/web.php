<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentenceInfoController;
use App\Http\Controllers\WbwAnalysisController;
use App\Http\Controllers\PageIndexController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;

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

Route::get('/', [PageIndexController::class, 'index']);

Route::get('/api/sentence/progress/image', [SentenceInfoController::class, 'showprogress']);
Route::get('/api/sentence/progress/daily/image', [SentenceInfoController::class, 'showprogressdaily']);
Route::get('/wbwanalyses', [WbwAnalysisController::class, 'index']);
Route::get('/attachments/{bucket}/{name}', [AssetsController::class, 'show']);

Route::get('/export/wbw', function () {
    return view('export_wbw', ['sentences' => []]);
});

Route::get('/privacy/{file}', function ($file) {
    $content = file_get_contents(base_path("/documents/mobile/privacy/{$file}.md"));
    return view('privacy', ['content' => $content]);
});

Route::get('/book/{id}', function ($id) {
    return view('book', ['id' => $id]);
});
Route::redirect('/privacy', '/privacy/index');




Route::post('/theme/toggle', [BookController::class, 'toggleTheme'])->name('theme.toggle');
Route::post('/logout', function () {
    // Handle logout
    //Auth::logout();
    return redirect('/login');
})->name('logout');

Route::prefix('library')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('library.home');
    Route::get('/category/{id}', [CategoryController::class, 'show'])->name('library.category.show');
    Route::get('/book/{id}', [BookController::class, 'show'])->name('library.book.show');
    Route::get('/book/{id}/read', [BookController::class, 'read'])->name('library.book.read');
});
// 博客路由
Route::prefix('blog')->group(function () {
    Route::get('/{user}', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/{user}/categories', [BlogController::class, 'categories'])->name('blog.categories');
    Route::get('/{user}/category/{category1}/{category2?}/{category3?}/{category4?}/{category5?}', [BlogController::class, 'category'])->name('blog.category');
    Route::get('/{user}/archives', [BlogController::class, 'archives'])->name('blog.archives');
    Route::get('/{user}/archives/{year}', [BlogController::class, 'archivesByYear'])->name('blog.archives.year');
    Route::get('/{user}/tag/{tag}', [BlogController::class, 'tag'])->name('blog.tag');
    Route::get('/{user}/search', [BlogController::class, 'search'])->name('blog.search');
    Route::get('/{user}/{post}', [BlogController::class, 'show'])->name('blog.show');
});

Route::group(['prefix' => 'tools'], function () {
    Route::get('/nissaya_format_converter', function () {
        return view('nissaya_format_converter');
    });
});
