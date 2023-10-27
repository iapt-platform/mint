<?php
/**
 * 更新段落关联数据库
 * 用于找到根本和义注复注的对应段落
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RelatedParagraph;
use App\Models\BookTitle;
use Illuminate\Support\Facades\Log;

class UpgradeRelatedParagraph extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:related.paragraph {book?}';

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
        $this->info("upgrade related.paragraph");
		$startTime = time();
        #删除目标数据库中数据
        RelatedParagraph::where('book','>',0)->delete();
		// 打开csv文件并读取数据
        $strFileName = config("mint.path.pali_title") . "/cs6_para.csv";
        if(!file_exists($strFileName)){
            return 1;
        }
        $inputRow = 0;
        $fp = fopen($strFileName, "r");
        if (!$fp ) {
            $this->error("can not open csv $strFileName");
            Log::error("can not open csv $strFileName");
        }
        $bookTitles = BookTitle::orderBy('sn','desc')->get();

        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if($inputRow>0){
                if(!empty($this->argument('book'))){
                    if($this->argument('book') !=$data[0] ){
                        continue;
                    }
                }
                //获取书号
                $bookId = 0;
                foreach ($bookTitles as $bookTitle) {
                    # code...
                    if((int)$data[0] === $bookTitle->book){
                        if((int)$data[1] >= $bookTitle->paragraph){
                            $bookId = $bookTitle->id;
                            break;
                        }
                    }
                }
                $begin = (int) $data[3];
                $end = (int) $data[4];
                $arrPara = array();
                for ($i = $begin; $i <= $end; $i++) {
                    $arrPara[] = $i;
                }
                foreach ($arrPara as $key => $para) {
                    $newRow = new RelatedParagraph();
                    $newRow->book = $data[0];
                    $newRow->para = $data[1];
                    $newRow->book_id = $bookId;
                    $newRow->cs_para = $para;
                    $newRow->book_name = $data[2];
                    $newRow->save();
                }
            }
            $inputRow++;
            if($inputRow % 1000 == 0){
                $this->info($inputRow);
            }
        }
        fclose($fp);
		$this->info("all done. in ". time()-$startTime . "s" );
        return 0;
    }
}
