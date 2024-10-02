<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Api\DictApi;

class UpgradeDictId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:wbw.dict.id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改wbw字典id';

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
        $this->info($this->description);
        $user_dict_id = DictApi::getSysDict('community');
        if($user_dict_id){
            $result = DB::select('UPDATE "user_dicts" set "dict_id"=? where "source"=? ',[$user_dict_id,'_USER_WBW_']);
        }else{
            $this->error('没有找到 community 字典');
        }

        $user_dict_extract_id = DictApi::getSysDict('community_extract');
        if($user_dict_extract_id){
            $result = DB::select('UPDATE "user_dicts" set "dict_id"=? where "source"=? ',[$user_dict_extract_id,'_SYS_USER_WBW_']);
        }else{
            $this->error('没有找到 community_extract 字典');
        }
        $this->info('all done');
        return 0;
    }
}
