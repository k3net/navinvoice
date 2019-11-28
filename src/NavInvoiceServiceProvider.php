<?php

namespace K3Net\NavInvoice;

use Illuminate\Support\ServiceProvider;

class NavInvoiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {   
        //ez az újabb verziókban van meg
        //$this->loadRoutesFrom(__DIR__.'/routes.php');
        //$this->loadMigrationsFrom(__DIR__.'/migrations');
      
        if (! $this->app->routesAreCached()) {
          require __DIR__.'/routes/routes.php';
        }
        
        $this->publishes([
          __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('K3Net\NavInvoice\Http\Controllers\NavInvoiceController');
    }
}
