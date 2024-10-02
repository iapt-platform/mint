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
use App\Http\Controllers\GrammarGuideController;
use App\Http\Controllers\WbwController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ProgressImgController;
use App\Http\Controllers\RecentController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\ArticleNavController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\SignUpController;
use App\Http\Controllers\TermSummaryController;
use App\Http\Controllers\NissayaCardController;
use App\Http\Controllers\SentInChannelController;
use App\Http\Controllers\ChannelIOController;
use App\Http\Controllers\ChapterIOController;
use App\Http\Controllers\SentenceIOController;
use App\Http\Controllers\WebHookController;
use App\Http\Controllers\DictStatisticController;
use App\Http\Controllers\SearchTitleController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\OfflineIndexController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\DictVocabularyController;
use App\Http\Controllers\DictInfoController;
use App\Http\Controllers\PgPaliDictDownloadController;
use App\Http\Controllers\SearchPaliDataController;
use App\Http\Controllers\SearchPaliWbwController;
use App\Http\Controllers\SearchPageNumberController;
use App\Http\Controllers\NavPageController;
use App\Http\Controllers\BookTitleController;
use App\Http\Controllers\SystemTermController;
use App\Http\Controllers\TermExportController;
use App\Http\Controllers\NavArticleController;
use App\Http\Controllers\NavCSParaController;
use App\Http\Controllers\SentencesInChapterController;
use App\Http\Controllers\CompoundController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InteractiveController;
use App\Http\Controllers\ChapterIndexController;
use App\Http\Controllers\WbwSentenceController;
use App\Http\Controllers\SnowFlakeIdController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\DiscussionCountController;
use App\Http\Controllers\TagsInChapterCountController;
use App\Http\Controllers\TagMapController;
use App\Http\Controllers\EditableSentenceController;
use App\Http\Controllers\ArticleFtsController;
use App\Http\Controllers\NissayaCoverController;
use App\Http\Controllers\AiTranslateController;

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
	Route::apiResource('terms-export',TermExportController::class);
	Route::get('terms-import',[TermExportController::class,'import']);
    Route::get('system-term/{lang}/{word}',[SystemTermController::class,"show"]);

	Route::apiResource('sentence',SentenceController::class);
	Route::apiResource('sent-in-channel',SentInChannelController::class);
	Route::apiResource('sentpr',SentPrController::class);
	Route::post('sent-pr-tree',[SentPrController::class,"pr_tree"]);
	Route::apiResource('progress',ProgressChapterController::class);
	Route::apiResource('tag',TagController::class);
	Route::apiResource('view',ViewController::class);

    Route::delete('like', [LikeController::class, 'delete']);
    Route::apiResource('like',LikeController::class);
    Route::apiResource('sent_history',SentHistoryController::class);
    Route::get('sent_history_contribution',[SentHistoryController::class,'contribution']);
    Route::apiResource('palitext',PaliTextController::class);
    Route::apiResource('channel',ChannelController::class);
    Route::patch('channel', [ChannelController::class,"patch"]);
    Route::get('channel-name/{name}', [ChannelController::class,"showByName"]);
    Route::get('channel-my-number', [ChannelController::class, 'showMyNumber']);
    Route::post('channel-progress',[ChannelController::class,"progress"]);
    Route::delete('userdict', [UserDictController::class, 'delete']);
    Route::apiResource('userdict',UserDictController::class);

    Route::apiResource('anthology',CollectionController::class);
    Route::get('anthology-my-number', [CollectionController::class, 'showMyNumber']);
    Route::apiResource('dict',DictController::class);
    Route::apiResource('article',ArticleController::class);
    Route::get('article-my-number', [ArticleController::class, 'showMyNumber']);
    Route::put('article-preview/{id}', [ArticleController::class, 'preview']);

    Route::apiResource('group',GroupController::class);
    Route::get('group-my-number', [GroupController::class, 'showMyNumber']);

    Route::get('auth/current',[AuthController::class,'getUserInfoByToken']);
    Route::post('sign-in',[AuthController::class,'signIn']);
    Route::apiResource('auth/forgot-password',ForgotPasswordController::class);
    Route::apiResource('auth/reset-password',ResetPasswordController::class);

    Route::apiResource('corpus',CorpusController::class);
    Route::get('corpus-sent/{id}',[CorpusController::class,'showSent']);
    Route::get('corpus-chapter/{id}',[CorpusController::class,'showChapter']);
    Route::get('corpus-sentences/{type}/{id}',[CorpusController::class,'showSentences']);

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
    Route::get('course-member-export',[CourseMemberController::class,"export"]);

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
    Route::apiResource('nissaya-card',NissayaCardController::class);
    Route::apiResource('relation',RelationController::class);
    Route::get('relation-export',[RelationController::class,"export"]);
    Route::get('relation-import',[RelationController::class,"import"]);
    Route::apiResource('term-vocabulary',TermVocabularyController::class);
    Route::apiResource('related-paragraph',RelatedParagraphController::class);
    Route::apiResource('search',SearchController::class);
    Route::get('search-book-list',[SearchController::class,'book_list']);
    Route::apiResource('pali-word-index',WordIndexController::class);
    Route::apiResource('studio',StudioController::class);
    Route::apiResource('grammar-guide',GrammarGuideController::class);
    Route::apiResource('wbw',WbwController::class);
    Route::apiResource('attachment',AttachmentController::class);
    Route::apiResource('api',ApiController::class);
    Route::apiResource('progress-img',ProgressImgController::class);
    Route::apiResource('recent',RecentController::class);
    Route::apiResource('milestone',MilestoneController::class);
    Route::apiResource('article-nav',ArticleNavController::class);
    Route::apiResource('invite',InviteController::class);
    Route::apiResource('sign-up',SignUpController::class);
    Route::apiResource('term-summary',TermSummaryController::class);

    Route::apiResource('channel-io',ChannelIOController::class);
    Route::apiResource('chapter-io',ChapterIOController::class);
    Route::apiResource('sentence-io',SentenceIOController::class);
    Route::apiResource('webhook',WebHookController::class);
    Route::apiResource('dict-statistic',DictStatisticController::class);
    Route::apiResource('search-title-index',SearchTitleController::class);
    Route::apiResource('transfer',TransferController::class);
    Route::apiResource('health-check',HealthCheckController::class);
    Route::apiResource('offline-index',OfflineIndexController::class);
    Route::apiResource('task',TaskController::class);
    Route::apiResource('export',ExportController::class);
    Route::apiResource('dict-vocabulary',DictVocabularyController::class);
    Route::apiResource('dict-info',DictInfoController::class);
    Route::apiResource('pg-pali-dict-download',PgPaliDictDownloadController::class);
    Route::apiResource('pali-search-data',SearchPaliDataController::class);
    Route::apiResource('search-pali-wbw',SearchPaliWbwController::class);
    Route::get('search-pali-wbw-books',[SearchPaliWbwController::class,'book_list']);
    Route::apiResource('search-page-number',SearchPageNumberController::class);
    Route::apiResource('nav-page',NavPageController::class);
    Route::apiResource('nav-article',NavArticleController::class);
    Route::apiResource('nav-cs-para',NavCSParaController::class);
    Route::apiResource('book-title',BookTitleController::class);
    Route::apiResource('sentences-in-chapter',SentencesInChapterController::class);
    Route::apiResource('compound',CompoundController::class);
    Route::apiResource('notification',NotificationController::class);
    Route::apiResource('interactive',InteractiveController::class);
    Route::apiResource('chapter-index',ChapterIndexController::class);
    Route::apiResource('wbw-sentence',WbwSentenceController::class);
    Route::apiResource('snowflake',SnowFlakeIdController::class);
    Route::apiResource('discussion-count',DiscussionCountController::class);
    Route::apiResource('tags-in-chapter',TagsInChapterCountController::class);
    Route::apiResource('tag-map',TagMapController::class);
    Route::apiResource('editable-sentence',EditableSentenceController::class);
    Route::apiResource('article-fts',ArticleFtsController::class);
    Route::apiResource('nissaya-cover',NissayaCoverController::class);
    Route::apiResource('ai-translate',AiTranslateController::class);


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
