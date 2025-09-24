<?php

namespace Tjall\Pagebuilder;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use FeenstraDigital\LaravelCMS\Pagebuilder\Commands\MakePagebuilderBlock;
use FeenstraDigital\LaravelCMS\Pagebuilder\Commands\MakePagebuilderShortcode;
use FeenstraDigital\LaravelCMS\Pagebuilder\ShortcodeProcessor;
use FeenstraDigital\LaravelCMS\Pagebuilder\Middleware\SetLocale;
use Illuminate\Routing\Router;

class PagebuilderServiceProvider extends PackageServiceProvider {
    public function configurePackage(Package $package): void {
        $package
            ->name('laravel-pagebuilder')
            ->discoversMigrations()
            ->hasRoute('web')
            ->hasCommands([
                MakePagebuilderBlock::class,
                MakePagebuilderShortcode::class
            ]);
    }

    public function bootingPackage() {
        $this->publishes([
            __DIR__.'/Filament/Resources' => app_path('Filament/Resources'),
        ], 'filament-resources');

        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', SetLocale::class);
    }

    public function packageBooted(): void {
        $this->app->register(DynamicPageServiceProvider::class);

        ShortcodeProcessor::init();
    }
}
