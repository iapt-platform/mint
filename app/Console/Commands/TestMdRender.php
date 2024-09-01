<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\MdRender;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Tools\Markdown;

class TestMdRender extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:md.render term --format=unity --driver=str
     * @var string
     */
    protected $signature = 'test:md.render {item?} {--format=html} {--driver=morus}';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        Log::info('md render start item='.$this->argument('item'));
        $data = array();
        $data['bold'] = <<<md
        **三十位** 经在[中间]六处为**[licchavi]**，在极果为**慧解脱**
        md;

        $data['sentence'] = <<<md
        {{168-916-2-9}}
        md;

        $data['link'] = <<<md
        aa `[link](wikipali.org/aa.php?view=b&c=d)` bb
        md;

        $data['term'] = <<<md
        ## term
        [[bhagavantu]]
        md;
        $data['noteMulti'] = <<<md
        ## heading

        [点击](http://127.0.0.1:3000/my/article/para/168-876?mode=edit&channel=00ae2c48-c204-4082-ae79-79ba2740d506&book=168&par=876)

        ----

        dfef

        ```
        bla **content**
        {{99-556-8-12}}
        bla **content**
        ```
        md;

        $data['note'] = '`bla **bold** _em_ bla`';
        $data['noteTpl'] = <<<md
        {{note|trigger=kacayana|text=bla **bold** _em_ bla}}
        md;

        $data['noteTpl2'] = <<<md
        {{note|trigger=kacayana|text={{99-556-8-12}}}}
        md;

        $data['trigger'] = <<<md
        ## heading
        ddd
        - title
          content-1
        - title-2

          content-2

        aaa bbb
        md;
        $data['exercise'] = <<<md
        {{168-916-10-37}}
        {{exercise|1|((168-916-10-37))}}
        {{exercise|
        id=1|
        content={{168-916-10-37}}
        }}
        {{exercise|
        id=2|
        content=# ddd}}
        md;

        $data['article'] = <<<md
        {{article|
        type=article|
        id=27ade9ad-2d0c-4f66-b857-e9335252cc08|
        title=第一章 戒律概说（Vinaya）|
        style=modal}}
        md;

        $data['footnote'] = <<<md
        # title
        content `note content` `note2 content`
        md;

        $data['paragraph'] = <<<md
        # title
        content

        {{168-916-10-37}}
        {{168-916-10-37}}

        the end
        md;

        $data['img'] = <<<md
        # title
        content

        ![aaa](/images/aaa.jpg)

        the end
        md;

        $data['empty'] = '';

        Markdown::driver($this->option('driver'));

        $format = $this->option('format');
        if(empty($format)){
            $formats = ['react','unity','text','tex','html','simple'];
        }else{
            $formats = [$format];
        }
        foreach ($formats as $format) {
            $this->info("format:{$format}");
            foreach ($data as $key => $value) {
                $_item = $this->argument('item');
                if(!empty($_item) && $key !==$_item){
                    continue;
                }
                $mdRender = new MdRender([
                    'format'=>$format,
                    'footnote'=>true,
                    'paragraph'=>true,
                ]);
                $output = $mdRender->convert($value,['00ae2c48-c204-4082-ae79-79ba2740d506']);
                echo $output;
            }
        }
        return 0;
    }
}
