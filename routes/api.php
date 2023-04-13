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
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DictController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\CorpusController;
use App\Http\Controllers\ArticleProgressController;
use App\Http\Controllers\ExportWbwController;
use App\Http\Controllers\WbwLookupController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseMemberController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ArticleMapController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\DictMeaningController;
use App\Http\Controllers\UserOperationDailyController;
use App\Http\Controllers\UserStatisticController;
use App\Http\Controllers\SentSimController;
use App\Http\Controllers\NissayaEndingController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\TermVocabularyController;
use App\Http\Controllers\RelatedParagraphController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WordIndexController;
use App\Http\Controllers\StudioController;

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
	Route::get('terms-export',[DhammaTermController::class,"export"]);
    Route::get('terms-import',[DhammaTermController::class,"import"]);
	Route::apiResource('sentence',SentenceController::class);
	Route::post('sent-in-channel',[SentenceController::class,"sent_in_channel"]);
	Route::apiResource('sentpr',SentPrController::class);
	Route::post('sent-pr-tree',[SentPrController::class,"pr_tree"]);
	Route::apiResource('progress',ProgressChapterController::class);
	Route::apiResource('tag',TagController::class);
	Route::apiResource('view',ViewController::class);

    Route::delete('like', [LikeController::class, 'delete']);
    Route::apiResource('like',LikeController::class);
    Route::apiResource('sent_history',SentHistoryController::class);
    Route::apiResource('palitext',PaliTextController::class);
    Route::apiResource('channel',ChannelController::class);
    Route::get('channel-my-number', [ChannelController::class, 'showMyNumber']);
    Route::post('channel-progress',[ChannelController::class,"progress"]);
    Route::delete('userdict', [UserDictController::class, 'delete']);
    Route::apiResource('userdict',UserDictController::class);

    Route::apiResource('anthology',CollectionController::class);
    Route::get('anthology-my-number', [CollectionController::class, 'showMyNumber']);
    Route::apiResource('dict',DictController::class);
    Route::apiResource('article',ArticleController::class);
    Route::get('article-my-number', [ArticleController::class, 'showMyNumber']);
    Route::apiResource('group',GroupController::class);

    Route::get('auth/current',[AuthController::class,'getUserInfoByToken']);
    Route::post('auth/signin',[AuthController::class,'signIn']);

    Route::apiResource('corpus',CorpusController::class);
    Route::get('corpus/sent/{id}',[CorpusController::class,'showSent']);
    Route::get('corpus/chapter/{id}/{mode}',[CorpusController::class,'showChapter']);
    Route::get('corpus_sent/{type}/{id}/{mode}',[CorpusController::class,'showSentences']);
    Route::apiResource('article-progress',ArticleProgressController::class);

    Route::post('export_wbw',[ExportWbwController::class,'index']);
    Route::apiResource('attachments',UploadController::class);
    Route::apiResource('discussion',DiscussionController::class);
    Route::post('sent-discussion-tree',[DiscussionController::class,"discussion_tree"]);
    Route::get('discussion-anchor/{id}',[DiscussionController::class,'anchor']);
    Route::apiResource('user',UserController::class);
    Route::apiResource('group-member',GroupMemberController::class);
    Route::apiResource('share',ShareController::class);
    Route::apiResource('wbwlookup',WbwLookupController::class);
    Route::apiResource('course',CourseController::class);
    Route::apiResource('course-member',CourseMemberController::class);
    Route::put('course-member_set-channel',[CourseMemberController::class,'set_channel']);
    Route::get('course-my-course', [CourseController::class, 'showMyCourseNumber']);
    Route::get('course-curr', [CourseMemberController::class, 'curr']);

    Route::apiResource('exercise',ExerciseController::class);
    Route::apiResource('article-map',ArticleMapController::class);
    Route::apiResource('vocabulary',VocabularyController::class);
    Route::apiResource('case',CaseController::class);
    Route::apiResource('dict-meaning',DictMeaningController::class);
    Route::apiResource('user-operation-daily',UserOperationDailyController::class);
    Route::apiResource('user-statistic',UserStatisticController::class);
    Route::apiResource('sent-sim',SentSimController::class);
    Route::apiResource('nissaya-ending',NissayaEndingController::class);
    Route::get('nissaya-ending-card',[NissayaEndingController::class,"nissaya_card"]);
    Route::get('nissaya-ending-export',[NissayaEndingController::class,"export"]);
    Route::get('nissaya-ending-import',[NissayaEndingController::class,"import"]);
    Route::get('nissaya-ending-vocabulary',[NissayaEndingController::class,"vocabulary"]);
    Route::apiResource('relation',RelationController::class);
    Route::get('relation-export',[RelationController::class,"export"]);
    Route::get('relation-import',[RelationController::class,"import"]);
    Route::apiResource('term-vocabulary',TermVocabularyController::class);
    Route::apiResource('related-paragraph',RelatedParagraphController::class);
    Route::apiResource('search',SearchController::class);
    Route::get('search-book-list',[SearchController::class,'book_list']);
    Route::apiResource('pali-word-index',WordIndexController::class);
    Route::apiResource('studio',StudioController::class);

    Route::get('download/{type1}/{type2}/{uuid}/{filename}', function ($type1,$type2,$uuid,$filename) {

        header("Content-Type: {$type1}/{$type1}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $content = Cache::get("download/tmp/{$uuid}");
        file_put_contents("php://output",$content);
    });

    Route::get('palibook/{file}', function ($file) {
        if($file==='default'){$file="defualt";}
        return file_get_contents(public_path("app/palicanon/category/{$file}.json"));
    });

    Route::get('guide/{lang}/{file}', function ($lang,$file) {
        $filename = public_path("app/users_guide/{$lang}/{$file}.md");
        if(file_exists($filename)){
            return json_encode(['ok'=>true,'message'=>'','data'=>file_get_contents($filename)]);
        }else{
            return json_encode(['ok'=>false,'message'=>"no file {$lang}/{$file}"]);
        }
    });

    Route::get('siteinfo/{locale}', function ($locale) {
        if (! in_array($locale, ['en', 'zh-Hans', 'zh-Hant'])) {
            App::setLocale('en');
        }else{
            App::setLocale($locale);
        }
        $site = [
            'logo'=> __("site.logo"),
            'title'=> __('site.title'),
            'subhead'=> __('site.subhead'),
            'keywords'=> __('site.keywords'),
            'description'=> __('site.description'),
            'copyright'=> __('site.copyright'),
            'author'=> [
                'name'=> __('site.author.name'),
                'email'=> __('site.author.email'),
                ],
            ];
        return json_encode($site);
    });


});
