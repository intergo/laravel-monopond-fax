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
    	// We can check to use the old or new one
    	if (config('fax.monopond_url') == 'old') {
    		return 'laravel-monopond-fax';
    	}
    	
        return 'monopond-fax';
    }
}