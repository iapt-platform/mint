<?php
/**
 * 计算章节的父子，前后关系
 * 输入： csv文件
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaliText;
use App\Models\BookTitle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpgradePaliText extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:palitext 168
     * @var string
     */
    protected $signature = 'upgrade:palitext {from?} {to?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'upgrade pali_texts paragraph infomation';

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
		$this->info("upgrade pali text");
		$startTime = time();

		$_from = $this->argument('from');
		$_to = $this->argument('to');
		if(empty($_from) && empty($_to)){
			$_from = 1;
			$_to = 217;
		}else if(empty($_to)){
			$_to = $_from;
		}
#载入文件列表
		$fileListFileName = config("mint.path.palitext_filelist");

		$filelist = array();

		if (($handle = fopen($fileListFileName, 'r')) !== false) {
			while (($filelist[] = fgetcsv($handle, 0, ',')) !== false) {
			}
		}

		$bar = $this->output->createProgressBar($_to-$_from+1);

		for ($from=$_from; $from <= $_to; $from++) {
			$inputRow = 0;
			$arrInserString = array();
			#载入csv数据
			$FileName = $filelist[$from-1][1];
			$csvFile = config("mint.path.pali_title") .'/'. $from.'_pali.csv';
			if (($fp = fopen($csvFile, "r")) !== false) {
                Log::info("csv load：" . $csvFile);
				while (($data = fgetcsv($fp, 0, ',')) !== false) {
					if ($inputRow > 0) {
						array_push($arrInserString, $data);
					}
					$inputRow++;
				}
				fclose($fp);

			} else {
				$this->error( "can not open csv file. filename=" . $csvFile. PHP_EOL) ;
				Log::error( "can not open csv file. filename=" . $csvFile) ;
				continue;
			}
			$title_data = PaliText::select(['book','paragraph','level','parent','toc','lenght'])
								->where('book',$from)->orderby('paragraph','asc')->get();

            $paragraph_count = count($title_data);
            $paragraph_info = array();
            $paragraph_info[] = array($from, -1, $paragraph_count, -1, -1, -1);


            for ($iPar = 0; $iPar < count($title_data); $iPar++) {
                $title_data[$iPar]["level"] = $arrInserString[$iPar][3];
            }

            for ($iPar = 0; $iPar < count($title_data); $iPar++) {
                $book = $from ;
                $paragraph = $title_data[$iPar]["paragraph"];
                $true_level = (int) $title_data[$iPar]["level"];

                if ((int) $title_data[$iPar]["level"] == 8) {
                    $title_data[$iPar]["level"] = 100;
                }
                $curr_level = (int) $title_data[$iPar]["level"];
                # 计算这个chapter的段落数量
                $length = -1;


                for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
                    $thislevel = (int) $title_data[$iPar1]["level"];
                    if ($thislevel <= $curr_level) {
                        $length = (int) $title_data[$iPar1]["paragraph"] - $paragraph;
                        break;
                    }
                }

                if ($length == -1) {
                    $length = $paragraph_count - $paragraph + 1;
                }

                /*
                上一个段落
                算法：查找上一个标题段落。而且该标题段落的下一个段落不是标题段落
                */
                $prev = -1;
                if ($iPar > 0) {
                    for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
                        if ($title_data[$iPar1]["level"] < 8 && $title_data[$iPar1+1]["level"]==100) {
                            $prev = $title_data[$iPar1]["paragraph"];
                            break;
                        }
                    }
                }
                /*
                下一个段落
                算法：查找下一个标题段落。而且该标题段落的下一个段落不是标题段落
                */
                $next = -1;
                if ($iPar < count($title_data) - 1) {
                    for ($iPar1 = $iPar + 1; $iPar1 < count($title_data)-1; $iPar1++) {
                        if ($title_data[$iPar1]["level"] <8 && $title_data[$iPar1+1]["level"]==100) {
                            $next = $title_data[$iPar1]["paragraph"];
                            break;
                        }
                    }
                }
                //查找parent
                $parent = -1;
                if ($iPar > 0) {
                    for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
                        if ($title_data[$iPar1]["level"] < $true_level) {
                            $parent = $title_data[$iPar1]["paragraph"];
                            break;
                        }
                    }
                }
                //计算章节包含总字符数
                $iChapter_strlen = 0;

                for ($i = $iPar; $i < $iPar + $length; $i++) {
                    $iChapter_strlen += $title_data[$i]["lenght"];
                }

                $newData = [
                    'level' => $arrInserString[$iPar][3],
                    'toc' => $arrInserString[$iPar][5],
                    'chapter_len' => $length,
                    'next_chapter' => $next,
                    'prev_chapter' => $prev,
                    'parent' => $parent,
                    'chapter_strlen'=> $iChapter_strlen,
                ];
                if((int)$arrInserString[$iPar][3] < 8){
                    $newData['title'] = strtolower($arrInserString[$iPar][6]);
                    $newData['title_en'] = \App\Tools\Tools::getWordEn($newData['title']);
                }

                $path = [];

                $title_data[$iPar]["level"] = $newData["level"];
                $title_data[$iPar]["toc"] = $newData["toc"];
                $title_data[$iPar]["parent"] = $newData["parent"];

                /*
                *获取路径
                */
                $currParent = $parent;

                $iLoop = 0;
                while ($currParent != -1 && $iLoop<7) {
                    # code...
                    $pathTitle = $title_data[$currParent-1]["toc"];
                    $pathLevel = $title_data[$currParent-1]['level'];
                    $path[] = ["book"=>$book,"paragraph"=>$currParent,"title"=>$pathTitle,"level"=>$pathLevel];
                    $currParent = $title_data[$currParent-1]["parent"];
                    $iLoop++;
                }

                //插入书名
                if(count($path)>0){
                    $bookPara = end($path)['paragraph'];
                }else{
                    $bookPara = $paragraph;
                }

                $pcd_book = BookTitle::where('book',$book)
                                    ->where('paragraph',$bookPara)
                                    ->first();
                if($pcd_book){
                    if(empty($pcd_book)){
                        Log::error('no pcd book:'.$book.'-'.$bookPara);
                    }
                    $book_id = $pcd_book->sn;
                    if(!empty($book_id)){
                        $newData['pcd_book_id'] = $book_id;
                    }
                    $path[] = ["book"=>$book_id,"paragraph"=>$book_id,"title"=>$pcd_book->title,"level"=>0];
                }

                # 将路径反向
                $path1 = [];
                for ($i=count($path)-1; $i >=0 ; $i--) {
                    # code...
                    $path1[] = $path[$i];
                }
                $newData['path'] = $path1;


                PaliText::where('book',$book)
                        ->where('paragraph',$paragraph)
                        ->update($newData);

                if ($curr_level > 0 && $curr_level < 8) {
                    $paragraph_info[] = array($book, $paragraph, $length, $prev, $next, $parent);
                }
            }
			$bar->advance();
		}
		$bar->finish();

		$this->info("instert pali text finished. in ". time()-$startTime . "s" );
		Log::info("instert pali text finished. in ". time()-$startTime . "s");
        return 0;
    }
}
