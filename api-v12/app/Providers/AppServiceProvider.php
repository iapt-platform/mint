<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\LaravelSequenceResolver;
use App\Tools\QueryBuilderMacro;
use Illuminate\Database\Query\Builder as QueryBuilder;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //雪花算法

        $this->app->singleton('snowflake', function () {
            return (new Snowflake(config('mint.snowflake_data_center_id'), config('mint.snowflake.worker_id')))
                ->setStartTimeStamp(strtotime(config('mint.snowflake.start')) * 1000)
                ->setSequenceResolver(
                    new LaravelSequenceResolver(
                        $this->app->get('cache')->store()
                    )
                );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        QueryBuilder::mixin($queryBuilderMacro = $this->app->make(QueryBuilderMacro::class));
    }
}
