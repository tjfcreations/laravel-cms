<?php

namespace FeenstraDigital\LaravelCMS;

use FeenstraDigital\LaravelCMS\Media\Commands\RegenerateMediaCommand;
use FeenstraDigital\LaravelCMS\Media\MediaServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use FeenstraDigital\LaravelCMS\Pagebuilder\Commands\MakePagebuilderBlock;
use FeenstraDigital\LaravelCMS\Pagebuilder\Commands\MakePagebuilderShortcode;

class LaravelCMSServiceProvider extends PackageServiceProvider {
    public function configurePackage(Package $package): void {
        $package
            ->name('laravel-cms')
            ->discoversMigrations()
            ->hasRoute('web')
            ->hasViews('media-gallery')
            ->hasCommands([
                // media
                RegenerateMediaCommand::class,

                // pagebuilder
                MakePagebuilderBlock::class,
                MakePagebuilderShortcode::class
            ]);
    }

    public function bootingPackage() {
        $this->app->register(MediaServiceProvider::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'media-gallery');
    }
}
