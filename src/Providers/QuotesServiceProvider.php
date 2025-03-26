<?php

namespace RH\Quotes\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use RH\Quotes\Services\QuoteService;

class QuotesServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Cargar configuración primero
        $this->mergeConfigFrom(__DIR__ . '/../../config/quotes.php', 'quotes');

        // Registrar servicio con configuración
        $this->app->singleton(QuoteService::class, function ($app) {
            return new QuoteService(
                config('quotes') // Debe retornar un array
            );
        });
    }

    public function boot()
    {
        // Configuración
        $this->publishes([
            __DIR__ . '/../../config/quotes.php' => config_path('quotes.php'),
        ], 'config');

        // Rutas
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Vistas
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'quotes');

        // Assets Vue
        $this->publishes([
            __DIR__ . '/../../dist' => public_path('vendor/quotes-ui'),
        ], 'quotes-ui-assets');
        
        // sources
        $this->publishes([
            __DIR__ . '/../../resources/css' => resource_path('css/vendor/quotes-ui'),
        ], 'quotes-ui-sources-css');
    }
}
