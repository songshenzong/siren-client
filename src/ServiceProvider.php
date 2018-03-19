<?php

namespace Songshenzong\Siren;

use function config;
use function dd;

/**
 * Class ServiceProvider
 *
 * @package Songshenzong\Siren
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
        $config = config('siren');
        if ($config) {
            Siren::setConfig($config);
        }

        $this->app->singleton('Siren', function () {
            return new Siren();
        });

        $this->app->alias('Siren', Facade::class);
    }


}
