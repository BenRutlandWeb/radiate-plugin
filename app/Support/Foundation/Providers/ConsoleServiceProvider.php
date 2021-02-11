<?php

namespace Plugin\Support\Foundation\Providers;

use Plugin\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The commands to register
     *
     * @var array
     */
    protected $commands = [
        \Plugin\Support\Events\Console\MakeEvent::class,
        \Plugin\Support\Events\Console\MakeListener::class,
        \Plugin\Support\Events\Console\MakeSubscriber::class,
        \Plugin\Support\Foundation\Console\MakeProvider::class,
        \Plugin\Support\Foundation\Console\VendorPublish::class,
        \Plugin\Support\Routing\Console\MakeController::class,
        \Plugin\Support\Routing\Console\MakeMiddleware::class,
    ];

    /**
     * Register the provider
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->commands as $command) {
            $this->app->singleton($command, function ($app) use ($command) {
                return new $command($app, $app['files']);
            });
        }
    }

    /**
     * Boot the services
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->commands as $command) {
            $this->app[$command]->register();
        }
    }
}
