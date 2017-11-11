<?php

namespace Shipu\SslWPayment;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class SslWPaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setupConfig();
    }
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerSslWPayment();
    }
    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/sslwpayment.php');
        // Check if the application is a Laravel OR Lumen instance to properly merge the configuration file.
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('sslwpayment.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('sslwpayment');
        }
        $this->mergeConfigFrom($source, 'sslwpayment');
    }
    /**
     * Register Talk class.
     */
    protected function registerSslWPayment()
    {
        $this->app->bind('payment', function (Container $app) {
            return new Payment($app['config']->get('sslwpayment'));
        });
        $this->app->alias('payment', Payment::class);
    }
    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'payment'
        ];
    }
}
