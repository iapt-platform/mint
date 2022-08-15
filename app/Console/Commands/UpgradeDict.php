<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Models\UserDict;
use App\Models\DictInfo;

class UpgradeDict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:dict {uuid?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入csv字典';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

	private function scandict($dir){
		if(is_dir($dir)){
			$this->info("scan:".$dir);
			if($files = scandir($dir)){
				//进入目录搜索字典或子目录
				foreach ($files as $file) {
					//进入语言目录循环搜索
					$fullPath = $dir."/".$file;
					if(is_dir($fullPath) && $file !== '.' && $file !== '..'){
						//是目录继续搜索
						$this->scandict($fullPath);
					}else{
						//是文件，查看是否是字典信息文件
						$infoFile = $fullPath;
						if(pathinfo($infoFile,PATHINFO_EXTENSION) === 'ini'){
							$dictInfo = parse_ini_file($infoFile,true);
							if(isset($dictInfo['meta']['dictname'])){
								//是字典信息文件
								$this->info($dictInfo['meta']['dictname']);
								if(Str::isUuid($this->argument('uuid'))){
									if($this->argument('uuid') !== $dictInfo['meta']['uuid']){
										continue;
									}
								}
								if(!Str::isUuid($dictInfo['meta']['uuid'])){
									$this->error("not uuid");
									continue;
								}
								$tableDict = DictInfo::firstOrNew([
									"id" => $dictInfo['meta']['uuid']
								]);
								$tableDict->id = $dictInfo['meta']['uuid'];
								$tableDict->name = $dictInfo['meta']['dictname'];
								$tableDict->shortname = $dictInfo['meta']['shortname'];
								$tableDict->description = $dictInfo['meta']['description'];
								$tableDict->src_lang = $dictInfo['meta']['src_lang'];
								$tableDict->dest_lang = $dictInfo['meta']['dest_lang'];
								$tableDict->rows = $dictInfo['meta']['rows'];
								$tableDict->owner_id = config("app.admin.root_uuid");
								$tableDict->meta = json_encode($dictInfo['meta']);
								$tableDict->save();

								UserDict::where("dict_id",$dictInfo['meta']['uuid'])->delete();
								$filename = $dir.'/'.pathinfo($infoFile,PATHINFO_FILENAME);
								$csvFile = $filename . ".csv";
								$count = 0;
								$bar = $this->output->createProgressBar($dictInfo['meta']['rows']);
								while (file_exists($csvFile)) {
									# code...
									$this->info("runing:{$csvFile}");
									$inputRow = 0;
									if (($fp = fopen($csvFile, "r")) !== false) {
										$cols = array();
										while (($data = fgetcsv($fp, 0, ',')) !== false) {
											if ($inputRow == 0) {
												foreach ($data as $key => $colname) {
													# 列名列表
													$cols[$colname] = $key;
												}
											}else{
												$word["id"]=app('snowflake')->id();
												$word["word"] = $data[$cols['word']];
												if(isset($cols['type'])) $word["type"] = $data[$cols['type']];
												if(isset($cols['grammar'])) $word["grammar"] = $data[$cols['grammar']];
												if(isset($cols['parent'])) $word["parent"] = $data[$cols['parent']];
												if(isset($cols['mean'])) $word["mean"] = $data[$cols['mean']];
												if(isset($cols['note'])) $word["note"] = $data[$cols['note']];
												if(isset($cols['factors'])) $word["factors"] = $data[$cols['factors']];
												if(isset($cols['factormean'])) $word["factormean"] = $data[$cols['factormean']];
												if(isset($cols['status'])) $word["status"] = $data[$cols['status']];
												if(isset($cols['language'])) $word["language"] = $data[$cols['language']];
												if(isset($cols['confidence'])) $word["confidence"] = $data[$cols['confidence']];
												$word["source"]='_PAPER_RICH_';
												$word["create_time"]=(int)(microtime(true)*1000);
												$word["creator_id"]=1;
												$word["dict_id"] = $dictInfo['meta']['uuid'];
												$id = UserDict::insert($word);
												$bar->advance();
											}
											$inputRow++;
										}
									}
									$count++;
									$csvFile = $filename . "{$count}.csv";
								}
								$bar->finish();
								$this->info("done");
							}
							
						}
					}
				}
				//子目录搜素完毕
				return;
			}else{
				//获取子目录失败
				$this->error("scandir fail");
				return;
			}
		}else{
			$this->error("this is not dir input={$dir}");
			return;
		}
	}
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		$this->info("upgrade dict start");
		$this->scandict(config("app.path.dict_text"));
		$this->info("upgrade dict done");

        return 0;
    }
}
