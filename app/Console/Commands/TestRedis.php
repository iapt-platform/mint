<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use App\Tools\RedisClusters;

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
		$value='this is a test';
		$this->info("test redis start");
        $remember = Cache::store('redis')->remember('dd',10,function(){
            return 'remember ok';
        });
        $this->info("test store remember value=".$remember);

        $key = "test-redis";
		Redis::set($key,$value);
        if(Redis::exists($key)){
            $this->info("has key ".$key);
        }else{
            $this->error("no key ".$key);
        }
        $expire = Redis::expire($key,10);
        $this->info("key expire ".$expire);
        $this->info('del key '.Redis::del($key));
		Redis::set($key,$value);
        $getValue = Redis::get($key);
		if($getValue === $value){
			$this->info("redis set ok ");
		}else{
			$this->error("redis set fail ");
		}


		Redis::hSet("test-redis-hash",'hash',$value);
		if(Redis::hGet("test-redis-hash",'hash')==$value){
			$this->info("redis hash set ok ");
		}else{
			$this->error("redis hash set fail ");
		}


		$this->info("test cache start");
		$this->info("testing cache put");
		$key = 'cache-key';
		Cache::put($key,$value,1000);
		if(Cache::has($key)){
			$this->info("cache::put() exist key={$key}");
			if(Cache::get($key)==$value){
				$this->info("cache::get() ok value={$value}");
			}else{
				$this->error("cache::get() fail ");
			}
		}else{
			$this->error('no key cache-key');
		}


		$key = 'cache-key-2';
		$this->info("testing cache() function");
		$this->info("cache() key={$key} value={$value}");
		cache(["cache-key-2"=>$value]);
		if(Cache::has($key)){
			if(Cache::get($key)==$value){
				$this->info("cache() get ok value={$value}");
			}else{
				$this->error("cache::get() fail ");
			}
		}else{
			$this->error('no key cache-key-2');
		}

		$key = 'cache-key-3';
		$this->info("testing cache remember()");
		$value = Cache::remember($key,600,function(){
			return 'cache-value-3';
		});
		if(Cache::has($key)){
			$this->info("{$key} exist value=".Cache::get($key));
		}else{
			$this->error("cache::remember() fail.");
		}

        $key = 'cache-key-clusters';
		$this->info("testing RedisClusters remember()");
        $this->info('get='.RedisClusters::get($key));
        RedisClusters::put($key,'RedisClusters');
        if(RedisClusters::has($key)){
            $this->info("RedisClusters has key value=".RedisClusters::get($key));
        }
        RedisClusters::forget($key);
        if(RedisClusters::has($key)){
            $this->error("RedisClusters forget fail ");
        }else{
            $this->info("RedisClusters forget successful ");
        }
        $null = null;
		$value = RedisClusters::remember($key,2,function() use($null){
			return $null;
		});
        $this->info("null=".$value);
        RedisClusters::forget($key);
        $value1 = ['data'=>'cache-key-clusters'];
		$value = RedisClusters::remember($key,2,function() use($value1){
			return $value1;
		});

		if(RedisClusters::has($key)){
			$this->info("{$key} exist value=");
            var_dump(RedisClusters::get($key));
            sleep(3);
            if(RedisClusters::has($key)){
                $this->error('exp fail');
            }else{
                $this->info('exp successful');
            }
		}else{
			$this->error("cache::remember() fail.");
		}
        return 0;
    }
}
