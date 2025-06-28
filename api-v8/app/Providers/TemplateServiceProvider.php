<?php

// ================== 服务提供者 ==================

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Template\TemplateService;

class TemplateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TemplateService::class, function ($app) {
            return new TemplateService(
                config('template.cache_enabled', true),
                config('template.cache_ttl', 3600)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/template.php' => config_path('template.php'),
        ], 'template-config');
    }
}
