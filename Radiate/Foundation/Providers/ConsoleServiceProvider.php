<?php

namespace Radiate\Foundation\Providers;

use Radiate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Boot the services
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            \Radiate\Foundation\Console\MakeProvider::class,
            \Radiate\Foundation\Console\VendorPublish::class,
        ]);
    }
}
