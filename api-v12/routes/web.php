<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\WbwAnalysisController;
use App\Http\Controllers\PageIndexController;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DownloadController;

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

Route::prefix('library')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('library.home');
    Route::get('/category/{id}', [CategoryController::class, 'show'])->name('library.category.show');
    Route::get('/book/{id}', [BookController::class, 'show'])->name('library.book.show');
    Route::get('/book/{id}/read', [BookController::class, 'read'])->name('library.book.read');
    Route::get('/wiki', [BookController::class, 'read'])->name('library.wiki');
    Route::get('/download', [DownloadController::class, 'index'])->name('library.download');
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
