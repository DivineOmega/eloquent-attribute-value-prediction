<?php

namespace DivineOmega\EloquentAttributeValuePrediction;

use DivineOmega\EloquentAttributeValuePrediction\Console\Commands\Train;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Train::class,
            ]);
        }
    }
}