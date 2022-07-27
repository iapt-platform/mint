<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class TestRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'testing redis';

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
		$this->info("test redis");
		Redis::set("test-redis",'this is a test');
		$this->info("redis get:".Redis::get("test-redis"));

		Redis::hSet("test-redis-hash",'hash','this is a test hash');
		$this->info("redis hash get:".Redis::hGet("test-redis-hash",'hash'));

		$this->info("test cache");
		$this->info("cache put key=cache-key value=cache-value");
		Cache::put('cache-key','cache-value',1000);
		if(Cache::has('cache-key')){
			$this->info('cache get: ',Cache::get('cache-key'));
		}else{
			$this->error('no key cache-key');
		}
		$this->info("test cache() function");
		$this->info("cache() key=cache-key-2 value=cache-value-2");
		cache(["cache-key-2"=>'cache-value-2']);
		if(Cache::has('cache-key-2')){
			$this->info('cache() get: ',Cache::get('cache-key-2'));
		}else{
			$this->error('no key cache-key-2');
		}

		$this->info("test cache remember()");
		$value = Cache::remember('cache-key-3',600,function(){
			return 'cache-value-3';
		});
		if(Cache::has('cache-key-3')){
			$this->info("cache-key-3 exist value=",Cache::get('cache-key-3'));
		}else{
			$this->error("cache::remember() fail.");
		}


        return 0;
    }
}
