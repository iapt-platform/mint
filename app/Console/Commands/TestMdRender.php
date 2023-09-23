<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\MdRender;
use Illuminate\Support\Str;

class TestMdRender extends Command
{
    /**
     * The name and signature of the console command.
     * run php artisan test:md.render term unity
     * @var string
     */
    protected $signature = 'test:md.render {item?} {--format=html}';

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
        $data = array();
        $data['bold'] = <<<md
        **三十位** 经
        在[中间]六处为**[[licchavi]]**，在极果为**慧解脱**
        md;

        $data['sentence'] = <<<md
        {{140-535-2-2}}
        md;

        $data['link'] = <<<md
        aa `[link](wikipali.org/aa.php?view=b&c=d)` bb
        md;

        $data['term'] = <<<md
        ## heading
        [[bhagavantu]]
        ```
        test
        ```
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
        $data['note'] = '`bla bla`';
        $data['noteTpl'] = <<<md
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

        //$wiki = MdRender::markdown2wiki($data['noteMulti']);
        //$xml = MdRender::wiki2xml($wiki,['00ae2c48-c204-4082-ae79-79ba2740d506']);
        //$this->info($xml);
        //$html = MdRender::markdownToHtml($xml);
        //$this->info($html);
        //$html = MdRender::xmlQueryId($xml, "1");
        //$sent = MdRender::take_sentence($html);
        //print_r($sent);

        foreach ($data as $key => $value) {
            $_item = $this->argument('item');
            if(!empty($_item) && $key !==$_item){
                continue;
            }
            $format = $this->option('format');
            echo MdRender::render2($value,
                                  ['00ae2c48-c204-4082-ae79-79ba2740d506'],
                                  null,'read','translation',
                                  $contentType="markdown",$format);
        }

        return 0;
    }
}
