<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Http\Api\Mq;
use App\Models\Discussion;
use App\Http\Resources\DiscussionResource;
use Illuminate\Support\Str;

class TestMq extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mq {--discussion}';

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

		Mq::publish('hello',['hello world']);
        $discussion = $this->option('discussion');
        if($discussion && Str::isUuid($discussion)){
            Mq::publish('discussion',new DiscussionResource(Discussion::find($discussion)));
        }

        return 0;
    }
}
