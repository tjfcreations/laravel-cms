<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder;

use FeenstraDigital\LaravelCMS\Pagebuilder\ShortcodeProcessor;
use FeenstraDigital\LaravelCMS\Locale\Http\Middleware\SetLocale;
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
        $this->app->register(DynamicPageServiceProvider::class);

        // initialize shortcode processor
        ShortcodeProcessor::init();
    }
}
