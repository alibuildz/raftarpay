<?php

namespace RaftarPay;

use Illuminate\Support\ServiceProvider;
use RaftarPay\Console\InstallCommand;

class RaftarPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/raftarpay.php', 'raftarpay');

        $this->app->singleton('raftarpay', function ($app) {
            return new PaymentManager($app['config']->get('raftarpay', []));
        });

        $this->app->alias('raftarpay', PaymentManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/raftarpay.php' => config_path('raftarpay.php'),
            ], 'raftarpay-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'raftarpay-migrations');

            $this->commands([InstallCommand::class]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (config('raftarpay.routes.enabled', true)) {
            $this->registerRoutes();
        }
    }

    protected function registerRoutes(): void
    {
        $this->app['router']->group([
            'prefix'     => config('raftarpay.routes.prefix', 'raftarpay'),
            'middleware' => config('raftarpay.routes.middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/raftarpay.php');
        });
    }
}
