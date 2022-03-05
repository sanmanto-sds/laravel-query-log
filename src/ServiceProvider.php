<?php

declare(strict_types=1);

namespace Codivapps\LaravelQueryLog;

use Codivapps\LaravelQueryLog\Listeners\{ConnectionLogger, QueryLogger};
use Illuminate\Database\Events\{QueryExecuted, TransactionBeginning, TransactionCommitted, TransactionRolledBack};
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/query-log.php', 'query-log');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();

        if ($this->app['config']['query-log']['enable']) {
            $this->bindEvents();
        }
    }

    /**
     * Publish config
     */
    private function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/query-log.php' => $this->app->configPath('query-log.php'),
            ], 'config');
        }
    }

    /**
     * Bind database events
     */
    private function bindEvents(): void
    {
        $this->app['events']->listen(QueryExecuted::class, [QueryLogger::class, 'handle']);

        $this->app['events']->listen(TransactionBeginning::class, [ConnectionLogger::class, 'handle']);

        $this->app['events']->listen(TransactionCommitted::class, [ConnectionLogger::class, 'handle']);

        $this->app['events']->listen(TransactionRolledBack::class, [ConnectionLogger::class, 'handle']);
    }
}