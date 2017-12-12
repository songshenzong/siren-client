<?php

namespace Songshenzong\SirenClient;

/**
 * Class ServiceProvider
 *
 * @package Songshenzong\SirenClient
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
        $this->app->singleton('SirenClient', function ($app) {
            return new SirenClient($app);
        });

        $this->app->alias('SirenClient', Facade::class);
    }
}
