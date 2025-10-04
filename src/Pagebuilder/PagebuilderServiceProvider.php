<?php

namespace Feenstra\CMS\Pagebuilder;

use Feenstra\CMS\Pagebuilder\Shortcodes\ShortcodeProcessor;
use Feenstra\CMS\I18n\Http\Middleware\SetLocale;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PagebuilderServiceProvider extends ServiceProvider {
    public function bootingPackage() {
        $this->publishes([
            __DIR__.'/Filament/Resources' => app_path('Filament/Resources'),
        ], 'filament-resources');

    }

    public function boot(): void {
        // register SetLocale middleware
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', SetLocale::class);

        // register page routes
        $this->app->register(DynamicRouteServiceProvider::class);
    }
}
