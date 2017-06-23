<?php 

namespace FaxBroadcast\LaravelMonopondFax\Facades;
use Illuminate\Support\Facades\Facade;

class LaravelMonopondFax extends Facade {
    /**
     * Return facade accessor
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-monopond-fax';
    }
}