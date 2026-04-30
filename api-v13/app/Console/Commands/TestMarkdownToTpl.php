<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Log;

class TestMarkdownToTpl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:markdown.tpl {item?}';

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
        $data['basic'] = <<<md
        # 去除烦恼的五种方法（一种分类）

        1 为了自己的利益而从别人处听法；
        2 为了自己的利益而开示自己听闻过的法；
        3 念诵听闻过、学习过的法；
        4 一次又一次地用心思维听闻过、学习过的法；
        5 在心中忆念自己适合的禅修业处，如十遍、十不净等。

        （无碍解道，义注，1，63）
        md;

        $data['tpl'] = <<<md
        为了自己的利益而从别人处听法；

        {{168-916-2-9}}
        md;

        $article = new ArticleController;

        foreach ($data as $key => $value) {
            $_item = $this->argument('item');
            if(!empty($_item) && $key !==$_item){
                continue;
            }
            $tpl = $article->toTpl($value,
                        'eb9e3f7f-b942-4ca4-bd6f-b7876b59a523',
                        [
                            'user_uid'=>'ba5463f3-72d1-4410-858e-eadd10884713',
                            'user_id'=>4,
                        ]
                    );
            var_dump($tpl);
        }
        return 0;
    }
}
