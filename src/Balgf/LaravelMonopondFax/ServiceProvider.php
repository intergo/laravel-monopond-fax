<?php 

namespace Balgf\LaravelMonopondFax;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use MonopondSOAPClientV2;
use MPENV;

class ServiceProvider extends BaseServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/monopond.php' => config_path('monopond.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/monopond.php', 'monopond'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('laravel-monopond-fax',function(){
            $environment = config('monopond.environment');
            return new MonopondSOAPClientV2_1(config('monopond.username'), config('monopond.password'), constant("MPENV::{$environment}"));
        });
    }

}
