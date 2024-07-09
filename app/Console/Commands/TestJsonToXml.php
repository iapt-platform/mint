<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\Tools;

class TestJsonToXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:json.to.xml';

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
        $array = [
            'pali'=>['status'=>'7','value'=>'bārāṇasiyaṃ'],
            'real'=>['status'=>'7','value'=>'bārāṇasiyaṃ'],
            'id'=>'p171-2475-10'
        ];
        $xml = Tools::JsonToXml($array);
        $this->info($xml);
        return 0;
    }
}
