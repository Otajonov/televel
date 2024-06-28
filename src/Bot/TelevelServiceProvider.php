<?php

namespace Televel\Bot;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class TelevelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/televel.php', 'televel');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/televel.php' => config_path('televel.php'),
        ], 'config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\SetupTelevelBot::class,
                Commands\ResetWebhook::class,
                Commands\ListTelevelBots::class,
                Commands\RemoveTelevelBot::class,
            ]);
        }

        // Register routes for configured bots
        if (!$this->app->runningInConsole()) {
            $this->registerBotRoutes();
        }
    }

    /**
     * Register Televel bot routes.
     *
     * @return void
     */
    protected function registerBotRoutes()
    {
        $config = config('televel.bots', []);

        foreach ($config as $bot => $botConfig) {
           // $token = $botConfig['token'];
            $routePath = "televel/{$bot}/{token}";
            $controllerName = ucfirst($bot) . 'TelevelController';

            Route::post($routePath, [
                'uses' => "App\Http\Controllers\\{$controllerName}@webhook",
                'as' => "televel.webhook.{$bot}",
            ]);
        }
    }
}
