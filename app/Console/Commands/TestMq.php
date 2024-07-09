<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use App\Http\Api\Mq;
use App\Models\Discussion;
use App\Http\Resources\DiscussionResource;
use App\Models\SentPr;
use App\Http\Resources\SentPrResource;

class TestMq extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:mq
     * @var string
     */
    protected $signature = 'test:mq {--discussion=} {--pr=}';

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
		Mq::publish('hello',['hello world']);
        $discussion = $this->option('discussion');
        if($discussion && Str::isUuid($discussion)){
            Mq::publish('discussion',new DiscussionResource(Discussion::find($discussion)));
        }

        $pr = $this->option('pr');
        if($pr && Str::isUuid($pr)){
            Mq::publish('suggestion',new SentPrResource(SentPr::where('uid',$pr)->first()));
        }

        return 0;
    }
}
