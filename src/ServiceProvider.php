<?php

namespace Songshenzong\SirenClient;
use function env;

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
        SirenClient::setHost(env('SIREN_HOST'), env('SIREN_PORT'));
        SirenClient::setToken(env('SIREN_TOKEN'));

        $this->app->singleton('SirenClient', function ($app) {
            return new SirenClient($app);
        });

        $this->app->alias('SirenClient', Facade::class);
    }
}
