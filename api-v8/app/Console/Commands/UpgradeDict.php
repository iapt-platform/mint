<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use App\Models\UserDict;
use App\Models\DictInfo;

class UpgradeDict extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan upgrade:dict
     * @var string
     */
    protected $signature = 'upgrade:dict {uuid?} {--part}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入csv字典';

	protected $dictInfo;
	protected $cols;

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
							$this->dictInfo = parse_ini_file($infoFile,true);
							if(isset($this->dictInfo['meta']['dictname'])){
								//是字典信息文件
								$this->info($this->dictInfo['meta']['dictname']);
								if(Str::isUuid($this->argument('uuid'))){
									if($this->argument('uuid') !== $this->dictInfo['meta']['uuid']){
										continue;
									}
								}
								if(!Str::isUuid($this->dictInfo['meta']['uuid'])){
									$this->error("not uuid");
									continue;
								}
                                //读取 description
                                $desFile = $dir."/description.md";
                                if(file_exists($desFile)){
                                    $description = file_get_contents($desFile);
                                }else{
                                    $description = $this->dictInfo['meta']['description'];
                                }
								$tableDict = DictInfo::firstOrNew([
									"id" => $this->dictInfo['meta']['uuid']
								]);
								$tableDict->id = $this->dictInfo['meta']['uuid'];
								$tableDict->name = $this->dictInfo['meta']['dictname'];
								$tableDict->shortname = $this->dictInfo['meta']['shortname'];
								$tableDict->description = $description;
								$tableDict->src_lang = $this->dictInfo['meta']['src_lang'];
								$tableDict->dest_lang = $this->dictInfo['meta']['dest_lang'];
								$tableDict->rows = $this->dictInfo['meta']['rows'];
								$tableDict->owner_id = config("mint.admin.root_uuid");
								$tableDict->meta = json_encode($this->dictInfo['meta']);
								$tableDict->save();

								if($this->option('part')){
									$this->info(" dict id = ".$this->dictInfo['meta']['uuid']);
								}else{
									$del = UserDict::where("dict_id",$this->dictInfo['meta']['uuid'])->delete();
									$this->info("delete {$del} rows dict id = ".$this->dictInfo['meta']['uuid']);
								}
                                /**
                                 * 允许一个字典拆成若干个小文件
                                 * 文件名 为 ***.csv , ***-1.csv , ***-2.csv
                                 *
                                 */
								$filename = $dir.'/'.pathinfo($infoFile,PATHINFO_FILENAME);
								$csvFile = $filename . ".csv";
								$count = 0;
								$bar = $this->output->createProgressBar($this->dictInfo['meta']['rows']);
								while (file_exists($csvFile)) {
									# code...
									$this->info("runing:{$csvFile}");
									$inputRow = 0;
									if (($fp = fopen($csvFile, "r")) !== false) {
										$this->cols = array();
										while (($data = fgetcsv($fp, 0, ',')) !== false) {
											if ($inputRow == 0) {
												foreach ($data as $key => $colname) {
													# 列名列表
													$this->cols[$colname] = $key;
												}
											}else{
												if($this->option('part')){
													//仅仅提取拆分零件
													$word = $this->get($data,'word');
													$factor1 = $this->get($data,'factors');
													$factor1 = \str_replace([' ','(',')','=','-','$'],"+",$factor1);
													foreach (\explode('+',$factor1)  as $part) {
														# code...
														if(empty($part)){
															continue;
														}
														if(isset($newPart[$part])){
															$newPart[$part][0]++;
														}else{
															$partExists = Cache::remember('dict/part/'.$part,config('cache.expire',1000),function() use($part){
																return UserDict::where('word',$part)->exists();
															});
															if(!$partExists){
															$count++;
															$newPart[$part] = [1,$word];
															$this->info("{$count}:{$part}-{$word}");
															}
														}

													}
												}else{
													$newDict = new UserDict();
													$newDict->id = app('snowflake')->id();
													$newDict->word = $data[$this->cols['word']];
													$newDict->type = $this->get($data,'type');
													$newDict->grammar = $this->get($data,'grammar');
													$newDict->parent = $this->get($data,'parent');
													$newDict->mean = $this->get($data,'mean');
													$newDict->note = $this->get($data,'note');
													$newDict->factors = $this->get($data,'factors');
													$newDict->factormean = $this->get($data,'factormean');
													$newDict->status = $this->get($data,'status');
													$newDict->language = $this->get($data,'language');
													$newDict->confidence = $this->get($data,'confidence');
													$newDict->source = $this->get($data,'source');
													$newDict->create_time =(int)(microtime(true)*1000);
													$newDict->creator_id = 1;
													$newDict->dict_id = $this->dictInfo['meta']['uuid'];
													$newDict->save();
												}

												$bar->advance();
											}
											$inputRow++;
										}
									}
									$count++;
									$csvFile = $filename . "-{$count}.csv";
								}
								$bar->finish();
								Storage::disk('local')->put("tmp/pm-part.csv", "part,count,word");
                                if(isset($newPart)){
                                    foreach ($newPart as $part => $info) {
                                        # 写入磁盘文件
                                        Storage::disk('local')->append("tmp/pm-part.csv", "{$part},{$info[0]},{$info[1]}");
                                    }
                                }
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
	 * 获取列的值
	 */
	protected function get($data,$colname,$defualt=""){
		if(isset($this->cols[$colname])){
			return $data[$this->cols[$colname]];
		}else if(isset($this->dictInfo['cols'][$colname])){
			return $this->dictInfo['cols'][$colname];
		}else{
			return $defualt;
		}
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
		$this->info("upgrade dict start");
		$this->scandict(config("mint.path.dict_text"));
		$this->info("upgrade dict done");

        return 0;
    }
}
