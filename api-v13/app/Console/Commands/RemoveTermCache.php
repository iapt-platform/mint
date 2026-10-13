<?php

namespace App\Console\Commands;

use App\Facades\RedisCache;
use App\Models\Channel;
use App\Tools\Tools;
use Illuminate\Console\Command;

class RemoveTermCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:term.cache {word?}';

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
        if (Tools::isStop()) {
            return 0;
        }
        $word = $this->argument('word');
        $channels = Channel::select('uid')->get();
        if (empty($word)) {

        } else {
            foreach ($channels as $key => $channel) {
                $key = "/term/{$channel}/{$word}";
                if (RedisCache::has($key)) {
                    $this->info('has:'.$key);
                    RedisCache::forget($key);
                }
            }
        }

        return 0;
    }
}
