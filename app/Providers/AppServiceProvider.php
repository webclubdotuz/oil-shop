<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use App\utils\helpers;
use Illuminate\Support\Facades\View;
use Config;
use Illuminate\Support\Facades\URL; 
use Illuminate\Support\Facades\Request;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /* Use https links instead http links */
        if (Request::server('HTTP_X_FORWARDED_PROTO') == 'https')
        {
            URL::forceScheme('https');
        }

        try {
            $helpers           = new helpers();
            $currency          = $helpers->Get_Currency();
            $symbol_placement  = $helpers->get_symbol_placement();
            
            View::share([
                'currency'         => $currency,
                'symbol_placement' => $symbol_placement,
            ]);


        } catch (\Exception $e) {

            return [];
    
        }

        Schema::defaultStringLength(191);
        if(isset($_COOKIE['language'])) {
			App::setLocale($_COOKIE['language']);
		} else {
			App::setLocale('en');
		}
    }
}
