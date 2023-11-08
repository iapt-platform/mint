<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\DictApi;
use App\Models\UserDict;

class ExportFtsPali extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:fts.pali';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出全文搜索用的巴利语词汇表';

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
        //irregular
        $dictId = ['4d3a0d92-0adc-4052-80f5-512a2603d0e8'];
         //regular
        $dictId[] = DictApi::getSysDict('system_regular');
        $long = ["ā","ī","ū"];
        $path = storage_path('app/export/fts');
        if(!is_dir($path)){
            $res = mkdir($path,0700,true);
            if(!$res){
                Log::error('mkdir fail path='.$exportDir);
                return 1;
            }
        }
        $fp = fopen($path.'/pali.syn','w') or die("Unable to open file!");
        $count=0;
        foreach ($dictId as $key => $value) {
            $words = UserDict::where('dict_id',$value)
                             ->select('word')
                             ->groupBy('word')->cursor();
            $this->info('word count='.count($words));
            foreach ($words as $key => $word) {
                $count++;
                if($count % 1000 === 0){
                    $this->info($count);
                }
                $parent = UserDict::where('dict_id',$value)
                             ->where('word',$word->word)
                             ->selectRaw('parent,char_length("parent")')
                             ->groupBy('parent')->orderBy('char_length','asc')->first();

                if($parent && !empty($parent->parent)){
                    $end = mb_substr($parent->parent,-1,null,"UTF-8");
                    if(in_array($end,["ā","ī","ū"])){
                        $head = mb_substr($parent->parent,0,mb_strlen($parent->parent)-1,"UTF-8");
                        $newEnd = str_replace(["ā","ī","ū"],["a","i","u"],$end);
                        $parentWord = $head.$newEnd;

                    }else{
                        $parentWord = $parent->parent;
                    }
                    fwrite($fp, $word->word.' '.$parentWord.PHP_EOL);
                }else{
                    $this->error('word no parent word='.$word->word);
                }
            }
        }
        fclose($fp);


        return 0;
    }
}