<?php

namespace Songshenzong\StatisticClient;

/**
 * Class ServiceProvider
 *
 * @package Songshenzong\StatisticClient
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('StatisticClient', function ($app) {
            return new StatisticClient($app);
        });

        $this->app->alias('StatisticClient', Facade::class);
    }
}
