<?php

namespace App\Providers;

use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\LaravelSequenceResolver;
use App\Tools\QueryBuilderMacro;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //雪花算法

		$this->app->singleton('snowflake', function () {
            return (new Snowflake(config('mint.snowflake_data_center_id'),config('mint.snowflake.worker_id')))
                ->setStartTimeStamp(strtotime(config('mint.snowflake.start'))*1000)
                ->setSequenceResolver(
                    new LaravelSequenceResolver($this->app->get('cache')->store()
                ));
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        QueryBuilder::mixin($queryBuilderMacro = $this->app->make(QueryBuilderMacro::class));
        EloquentBuilder::mixin($queryBuilderMacro);
        Relation::mixin($queryBuilderMacro);
    }
}
