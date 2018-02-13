<?php

namespace Songshenzong\SirenClient;

use function config;

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
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
                             __DIR__ . '/../config/siren.php' => config_path('siren.php'),
                         ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        SirenClient::setHost(config('siren.client.host'), config('siren.client.port'));
        SirenClient::setToken(config('siren.client.token'));

        $this->app->singleton('SirenClient', function ($app) {
            return new SirenClient();
        });

        $this->app->alias('SirenClient', Facade::class);
    }
}
