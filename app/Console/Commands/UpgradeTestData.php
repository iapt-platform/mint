<?php
/**
 * 局部刷新语料库
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpgradeTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:test.data {book}';

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
        $this->call('init:cs6sentence',[$this->argument('book')]);
        $this->call('upgrade:wbw.template',[$this->argument('book')]);
        $this->call('upgrade:chapter.dynamic.weekly',["--book"=>$this->argument('book'),"--offset"=>300]);
        $this->call('upgrade:palitext',[$this->argument('book')]);
        $this->call('upgrade:compound',["--book"=>$this->argument('book')]);
        return 0;
    }
}
