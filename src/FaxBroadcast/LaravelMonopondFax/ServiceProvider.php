<?php 

namespace FaxBroadcast\LaravelMonopondFax;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use MonopondSOAPClientV2_1;
use FaxBroadcast\LaravelMonopondFax\MPENV;

class ServiceProvider extends BaseServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('monopond-fax',function(){
            $environment = config('monopond.environment');
            return new MonopondSOAPClientV2_1(config('monopond.username'), config('monopond.password'), constant("MPENV::{$environment}"));
        });
    }

}
