<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\WbwAnalysisController;
use App\Http\Controllers\PageIndexController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\Library\AnthologyController;
use App\Http\Controllers\Library\AnthologyReadController;
use App\Http\Controllers\Library\BookController;
use App\Http\Controllers\Library\WikiController;
use App\Http\Controllers\Library\SearchController;
use App\Http\Controllers\Library\HomeController;
use App\Http\Controllers\Library\TipitakaController;

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

Route::get('/wbwanalyses', [WbwAnalysisController::class, 'index']);
Route::get('/attachments/{bucket}/{name}', [AssetsController::class, 'show']);

Route::get('/export/wbw', function () {
    return view('export_wbw', ['sentences' => []]);
});


Route::get('/privacy/{file}', function (string $file) {
    $path = base_path("documents/mobile/privacy/{$file}.md");

    abort_unless(File::exists($path), 404);

    return view('privacy', [
        'content' => File::get($path),
    ]);
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

Route::prefix('library')->name('library.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/tipitaka', [TipitakaController::class, 'index'])->name('tipitaka.index');
    Route::get('/tipitaka/category/{id}', [TipitakaController::class, 'index'])->name('tipitaka.category');
    Route::get('/tipitaka/{id}', [BookController::class, 'show'])->name('tipitaka.show');
    Route::get('/tipitaka/{id}/read', [BookController::class, 'read'])->name('tipitaka.read');

    Route::get('/wiki', [WikiController::class, 'home'])->name('wiki.home');
    Route::get('/wiki/{lang}', [WikiController::class, 'index'])->name('wiki.index');
    Route::get('/wiki/{lang}/{word}', [WikiController::class, 'show'])->name('wiki.show');

    Route::get('/course', [DownloadController::class, 'index'])->name('course');
    Route::get('/download', [DownloadController::class, 'index'])->name('download');
    // 文集
    Route::get('/anthology',          [AnthologyController::class, 'index'])->name('anthology.index');
    Route::get('/anthology/{id}',     [AnthologyController::class, 'show'])->name('anthology.show');
    Route::get(
        '/anthology/{anthology}/read/{article}',
        [AnthologyReadController::class, 'read']
    )->name('anthology.read');

    Route::get('/search', [SearchController::class, 'search'])->name('search');
});
// 博客路由
Route::prefix('blog')->group(function () {
    Route::get('/{user}', [BlogController::class, 'index'])->name('blog.index')->where('user', '[a-zA-Z0-9_-]+');
    Route::get('/{user}/categories', [BlogController::class, 'categories'])->name('blog.categories');
    Route::get('/{user}/category/{category1}/{category2?}/{category3?}/{category4?}/{category5?}', [BlogController::class, 'category'])->name('blog.category');
    Route::get('/{user}/archives', [BlogController::class, 'archives'])->name('blog.archives');
    Route::get('/{user}/archives/{year}', [BlogController::class, 'archivesByYear'])->name('blog.archives.year');
    Route::get('/{user}/tag/{tag}', [BlogController::class, 'tag'])->name('blog.tag');
    Route::get('/{user}/search', [BlogController::class, 'search'])->name('blog.search');
    Route::get('/{user}/{post}', [BlogController::class, 'show'])
        ->name('blog.show')
        ->where([
            'user' => '[a-zA-Z0-9_-]+',
            'post' => '[a-zA-Z0-9_-]+',
        ]);
});

Route::group(['prefix' => 'tools'], function () {
    Route::get('/nissaya_format_converter', function () {
        return view('nissaya_format_converter');
    });
});
