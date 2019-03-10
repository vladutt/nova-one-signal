<?php

namespace Yassi\NovaOneSignal;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Yassi\NovaOneSignal\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nova-one-signal');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nova-one-signal');

        $this->publishes([__DIR__ . '/../resources/lang' => resource_path('lang/vendor/nova-one-signal')], 'nova-one-signal-lang');

        $this->app->booted(function () {
            $this->routes();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/one_signal.php' => base_path('config/one_signal.php')], 'config');
        }

    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/nova-one-signal')
            ->group(__DIR__ . '/../routes/api.php');
    }

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
     * Get the translation keys from file.
     *
     * @return array
     */
    private static function getTranslations()
    {
        $translationFile = resource_path('lang/vendor/nova-one-signal/' . app()->getLocale() . '.json');

        if (!is_readable($translationFile)) {
            return [];
        }

        return json_decode(file_get_contents($translationFile), true);
    }
}
