<?php

namespace App\Console\Commands;

use App\Models\DictInfo;
use Illuminate\Console\Command;

class InitSystemDict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:system.dict';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create system dict. like sys_regular  ect.';

    /**
     * name 不要修改。因为在程序其他地方，用name 查询词典id
     */
    protected $dictionary =[
        [
            "name"=>'robot_compound',
            'shortname'=>'compound',
            'description'=>'split compound by AI',
            'src_lang'=>'pa',
            'dest_lang'=>'cm',
        ],
        [
            "name"=>'system_regular',
            'shortname'=>'regular',
            'description'=>'system regular',
            'src_lang'=>'pa',
            'dest_lang'=>'cm',
        ],
        [
            "name"=>'community',
            'shortname'=>'社区',
            'description'=>'由用户贡献词条的社区字典',
            'src_lang'=>'pa',
            'dest_lang'=>'cm',
        ],
        [
            "name"=>'community_extract',
            'shortname'=>'社区汇总',
            'description'=>'由用户贡献词条的社区字典汇总统计',
            'src_lang'=>'pa',
            'dest_lang'=>'cm',
        ],
    ];

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
        $this->info("start");
        foreach ($this->dictionary as $key => $value) {
            # code...
            $channel = DictInfo::firstOrNew([
                'name' => $value['name'],
                'owner_id' => config("mint.admin.root_uuid"),
            ]);
            $channel->shortname = $value['shortname'];
            $channel->description = $value['description'];
            $channel->src_lang = $value['src_lang'];
            $channel->dest_lang = $value['dest_lang'];
            $channel->meta = json_encode($value,JSON_UNESCAPED_UNICODE);
            $channel->save();
            $this->info("updated {$value['name']}");
        }
        return 0;
    }
}
