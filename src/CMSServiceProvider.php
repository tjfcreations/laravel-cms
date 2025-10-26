<?php

namespace Feenstra\CMS;

use Feenstra\CMS\Commands\SetupCommand;
use Feenstra\CMS\Media\Commands\RegenerateMediaCommand;
use Feenstra\CMS\Media\MediaServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Feenstra\CMS\Pagebuilder\Commands\MakePagebuilderBlock;
use Feenstra\CMS\Pagebuilder\Commands\MakePagebuilderShortcode;
use Feenstra\CMS\Pagebuilder\PagebuilderServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Feenstra\CMS\Exceptions\Handler;
use Feenstra\CMS\Common\CommonServiceProvider;

class CMSServiceProvider extends PackageServiceProvider {
    public function configurePackage(Package $package): void {
        $package
            ->name('fd-cms')
            ->discoversMigrations()
            ->hasRoute('web')
            ->hasViews('fd-cms')
            ->hasConfigFile('fd-cms')
            ->hasCommands([
                SetupCommand::class,

                // media
                RegenerateMediaCommand::class,

                // pagebuilder
                MakePagebuilderBlock::class,
                MakePagebuilderShortcode::class
            ]);
    }

    public function bootingPackage() {
        $this->app->register(MediaServiceProvider::class);
        $this->app->register(PagebuilderServiceProvider::class);
        $this->app->register(CommonServiceProvider::class);

        $this->app->singleton(ExceptionHandler::class, Handler::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fd-cms');

        // register helper functions
        require_once __DIR__ . '/helpers.php';
    }
}
