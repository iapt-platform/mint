<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliText;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Support\Facades\Log;

class UpgradePaliTextTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:palitexttag {book?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade palitext tag';

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
        $this->info("upgrade pali text tag");
        $startTime = time();

        #载入csv数据
        $csvFile = config("mint.path.pali_title") .'/pali_text_tag.csv';
        if (($fp = fopen($csvFile, "r")) === false) {
            $this->error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
            Log::error( "can not open csv file. filename=" . $csvFile) ;
        }
        Log::info("csv load:" . $csvFile);
        $inputRow = 0;
        $tagCount = 0;
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            $inputRow++;
            if($inputRow%100==0){
                $this->info($inputRow);
            }

            //略过第一行标题行
            if ($inputRow == 1){
                continue;
            }
            /*测试第一行
            if($inputRow > 2) {
                break;
            }
            */
            $book = $data[0];
            if(!empty($this->argument('book'))){
                if($book != (int)$this->argument('book')){
                    continue;
                }
            }
            $para = $data[1];
            $tags = explode(':',$data[4]);
            $paliTextUuid = PaliText::where("book",$book)->where("paragraph",$para)->value('uid');
            if($paliTextUuid){
                //删除旧数据
                $tagMapDelete = TagMap::where('table_name' , 'pali_texts')
                                        ->where('anchor_id' , $paliTextUuid)
                                        ->delete();
                foreach ($tags as $key => $tag) {
                    # code...
                    if(!empty($tag)){
                        $tagRow = Tag::firstOrCreate(['name'=>$tag],['owner_id'=>config("mint.admin.root_uuid")]);
                        $tagmap = TagMap::firstOrCreate([
                                    'table_name' => 'pali_texts',
                                    'anchor_id' => $paliTextUuid,
                                    'tag_id' => $tagRow->id
                                ]);
                        if($tagmap){
                            $tagCount++;
                        }
                    }
                }
            }else{
                    $this->error("no palitext uuid book=$book para=$para ");
            }

        }
        fclose($fp);
        $this->info(" $inputRow para $tagCount tags  finished. in ". time()-$startTime . "s");
		Log::info("$inputRow para $tagCount tags  finished. in ". time()-$startTime . "s");
        return 0;
    }
}
