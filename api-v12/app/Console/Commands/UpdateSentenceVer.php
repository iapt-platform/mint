<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use Illuminate\Support\Str;

class UpdateSentenceVer extends Command
{
    /**
     * 将无channel_uid的旧版句子数据的ver修改为1.
     * php artisan update:sentence.ver
     * @var string
     */
    protected $signature = 'update:sentence.ver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将无channel_uid的旧版句子数据的ver修改为1';

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
        $count = 0;
        $total = Sentence::whereNull('channel_uid')->orWhere('channel_uid','')->count();
        foreach (Sentence::whereNull('channel_uid')->orWhere('channel_uid','')->cursor() as $key => $value) {
            # code...
            $value->ver = 1;
            $value->channel_uid = Str::uuid();
            $value->save();
            $count++;
            if($count % 1000 === 0){
                $per = (int)($count*100 / $total);
                $this->info("[{$per}%]-{$count}");
            }
        }
        $this->info("all done [{$count}]");
        return 0;
    }
}
