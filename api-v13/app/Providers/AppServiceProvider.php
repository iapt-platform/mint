<?php

namespace App\Providers;

use App\Services\PaliSeriesesService;
use App\Services\RomanizeService;
use App\Services\UserService;
use App\Tools\QueryBuilderMacro;
use App\View\Composers\BlogViewComposer;
use Carbon\CarbonImmutable;
use Godruoyi\Snowflake\LaravelSequenceResolver;
use Godruoyi\Snowflake\Snowflake;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Snowflake ID Generator
        |--------------------------------------------------------------------------
        */
        $this->app->singleton('snowflake', function () {
            return (new Snowflake(
                config('mint.snowflake.data_center_id'),
                config('mint.snowflake.worker_id')
            ))
                ->setStartTimeStamp(
                    strtotime(config('mint.snowflake.start')) * 1000
                )
                ->setSequenceResolver(
                    new LaravelSequenceResolver(
                        $this->app->get('cache')->store()
                    )
                );
        });

        /*
        |--------------------------------------------------------------------------
        | Romanize Service
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(RomanizeService::class);

        /*
        |--------------------------------------------------------------------------
        | Pali Serieses Service
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(PaliSeriesesService::class);

        /*
        |--------------------------------------------------------------------------
        | User Service
        |--------------------------------------------------------------------------
        |
        | 必须是 singleton：它在请求内维护一张 uuid => 摘要的身份映射，同一个用户
        | 在一次请求里被解析多次时只查一次库、只签一次头像 URL。每次 make 新实例
        | 那张映射就永远是空的。
        |
        */
        $this->app->singleton(UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Laravel 13 Default Production Behaviors
        |--------------------------------------------------------------------------
        */
        $this->configureDefaults();

        /*
        |--------------------------------------------------------------------------
        | Custom Query Builder Macros
        |--------------------------------------------------------------------------
        */
        QueryBuilder::mixin(
            $this->app->make(QueryBuilderMacro::class)
        );

        /*
        |--------------------------------------------------------------------------
        | View Composers
        |--------------------------------------------------------------------------
        */
        View::composer('blog.*', BlogViewComposer::class);
        View::composer('layouts.blog', BlogViewComposer::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Use Immutable Dates
        |--------------------------------------------------------------------------
        */
        Date::use(CarbonImmutable::class);

        /*
        |--------------------------------------------------------------------------
        | Prevent destructive DB commands in production
        |--------------------------------------------------------------------------
        */
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        /*
        |--------------------------------------------------------------------------
        | Strong password defaults in production
        |--------------------------------------------------------------------------
        */
        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}
