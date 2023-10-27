<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Relation;

class UpdateRelationTo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:relation.to';

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
        $count=0;
        $all=0;
        foreach (Relation::select(['id','to'])->cursor() as $relation) {
            $all++;
            if(!empty($relation->to)){
                $old = json_decode($relation->to,true);
                if(count(array_filter(array_keys($old),'is_string'))===0){
                    //索引数组，需要转换
                    $new = ['case'=>$old];
                    Relation::where('id',$relation->id)->update(['to'=>json_encode($new)]);
                    $count++;
                }
            }
        }
        $this->info("{$count} of {$all}");

        return 0;
    }
}
