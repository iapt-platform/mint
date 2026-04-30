<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\LaravelSequenceResolver;

use App\Tools\QueryBuilderMacro;
use Illuminate\Database\Query\Builder as QueryBuilder;

use App\Services\RomanizeService;

use Illuminate\Support\Facades\View;
use App\View\Composers\BlogViewComposer;

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
            fn(): ?Password => app()->isProduction()
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
