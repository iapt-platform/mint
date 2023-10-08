<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressChapter;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use App\Tools\Export;
use Illuminate\Support\Facades\Log;

class ExportChapter extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan export:chapter 213 1849 a19eaf75-c63f-4b84-8125-1bce18311e23
     * @var string
     */
    protected $signature = 'export:chapter {book} {para} {channel} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $book = $this->argument('book');
        $para = $this->argument('para');
        $channelId = $this->argument('channel');
        $channel = ChannelApi::getById($channelId);
        $chapter = PaliText::where('book',$book)->where('paragraph',$para)->first();
        if(!$chapter){
            return $this->error("no data");
        }
        $bookMeta = array();
        if(empty($chapter->toc)){
            $bookMeta['title'] = "unknown";
        }else{
            $title = ProgressChapter::where('book',$book)->where('para',$para)
                                    ->where('channel_id',$channelId)
                                    ->value('title');
            $bookMeta['book_title'] = $title?$title:$chapter->toc;
            $bookMeta['sub_title'] = $chapter->toc;
        }
        if($channel){
            $bookMeta['book_author'] = $channel['name'];
        }
        $subChapter = PaliText::where('book',$book)->where('parent',$para)
                              ->where('level','<',8)
                              ->orderBy('paragraph')
                              ->get();
        $sections = array();
        foreach ($subChapter as $key => $sub) {
            # code...
            $chapter = ProgressChapter::where('book',$book)->where('para',$sub->paragraph)
                                    ->where('channel_id',$channelId)
                                    ->first();
            if($chapter){
                $filename = "{$sub->paragraph}.tex";
                $bookMeta['sections'][] = ['filename'=>$filename];
                $paliTitle = PaliText::where('book',$book)->where('paragraph',$sub->paragraph)->value('toc');
                $title = $chapter->title?$chapter->title:$paliTitle;
                $content = array();

                $chapterStart = $sub->paragraph+1;
                $chapterEnd = $sub->paragraph + $sub->chapter_len;
                $chapterBody = PaliText::where('book',$book)
                                          ->whereBetween('paragraph',[$chapterStart,$chapterEnd])
                                          ->orderBy('paragraph')->get();
                foreach ($chapterBody as $body) {
                    # code...
                    $translationData = Sentence::where('book_id',$book)
                                    ->where('paragraph',$body->paragraph)
                                    ->where('channel_uid',$channelId)
                                    ->orderBy('word_start')->get();
                    $sentContent = array();
                    foreach ($translationData as $sent) {
                        $texText = MdRender::render($sent->content,
                                                        [$sent->channel_uid],
                                                        null,
                                                        'read',
                                                        $channel['type'],
                                                        $sent->content_type,
                                                        'tex'
                                                        );
                        $sentContent[] = trim($texText);
                    }
                    $paraContent = implode(' ',$sentContent);
                    if($body->level > 7){
                        $content[] = '\par '.$paraContent;
                    }else{
                        $currLevel = $body->level - $sub->level;
                        if($currLevel>0){
                            if(empty($paraContent)){
                                $subSessionTitle = PaliText::where('book',$book)
                                                     ->where('paragraph',$body->paragraph)
                                                     ->value('toc');
                            }else{
                                $subSessionTitle = $paraContent;
                            }
                            $subStr = array_fill(0,$currLevel,'sub');
                            $content[] = '\\'. implode('',$subStr) . "section{".$subSessionTitle.'}';
                        }else{
                            $content[] = '\par '.$paraContent;
                        }
                    }
                    $content[] = "\n";
                }

                $sections[] = [
                                'name'=>$filename,
                                'body'=>[
                                    'title'=>$title,
                                    'content'=>implode('',$content)
                                ]
                            ];
            }
        }
        $tex = array();
        $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,'delimiters' => '[[ ]]','escape'=>function ($value){
            return $value;
        }));
        $tpl = file_get_contents(resource_path("mustache/tex/main.tex"));
        $texContent = $m->render($tpl,$bookMeta);
        $tex[] = ['name'=>'main.tex',
                  'content'=>$texContent
                 ];
        foreach ($sections as $key => $section) {
            $tpl = file_get_contents(resource_path("mustache/tex/section.tex"));
            $texContent = $m->render($tpl,$section['body']);
            $tex[] = ['name'=>$section['name'],
                  'content'=>$texContent
                 ];
        }

        if($this->option('debug')){
            $dir = "export/{$book}-{$para}-{$channelId}/";
            foreach ($tex as $key => $section) {
                Storage::disk('local')->put($dir.$section['name'], $section['content']);
            }
        }
        $data = Export::ToPdf($tex);
        if($data['ok']){
            $filename = "export/{$book}-{$para}-{$channelId}.pdf";
            $this->info($data['content-type']);
            Storage::disk('local')->put($filename, $data['data']);
        }else{
            $this->error($data['code'].'-'.$data['message']);
        }

        return 0;
    }
}
