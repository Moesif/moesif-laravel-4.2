<?php namespace MoesifLaravel42\MoesifLaravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use MoesifLaravel42\MoesifLaravel\Middleware\Moesif;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('moesif/moesif-laravel42', 'moesif');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->middleware(Moesif::class, [$this->app]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
}
