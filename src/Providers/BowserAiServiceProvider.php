<?php

namespace Ruslanstarikov\BowserAi\Providers;

use Illuminate\Support\ServiceProvider;
use Ruslanstarikov\BowserAi\Commands\MakeToolCommand;

class BowserAiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bowser-ai.php', 'bowser-ai');
    }

    public function boot()
    {
        // Publish Config File
        $this->publishes([
            __DIR__.'/../config/bowser-ai.php' => config_path('bowser-ai.php'),
        ], 'bowser-ai-config');

        // Publish Blade Templates for tools
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/bowser-ai'),
        ], 'bowser-ai-views');

        // Load Views from Package
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'bowser-ai');

        // Register Artisan Command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Ruslanstarikov\BowserAi\Commands\MakeToolCommand::class,
            ]);
        }
    }
}