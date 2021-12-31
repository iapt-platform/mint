<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InitDependence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:dep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'init dependence date - dictionay,pali sencence';

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
		#克隆依赖的数据到本地
		#巴利句子
		$git['pali_sentence'] = env('DEPENDENCE_PALI_SENT', 'http://visuddhinanda.github.com/pali_sent/.git');
		#巴利相似句
		$git['pali_similarity'] = env('DEPENDENCE_PALI_SIM', 'http://visuddhinanda.github.com/pali_sim/.git');
		#单词分析
		$git['word_statistics'] = env('DEPENDENCE_WORD_STATI', 'http://visuddhinanda.github.com/word_statistics/.git');
		foreach ($git as $key => $value) {
			# clone repo to local
			$process = new Process(['git','clone',$pali_sent,storage_path($key)]);
			$process->run();
			if(!$process->isSuccessful()){
				throw new ProcessFailedException($process);
			}
			$this->info($process->getOutput());	
		}
        return 0;
    }
}
