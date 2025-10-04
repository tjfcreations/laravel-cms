<?php

namespace Feenstra\CMS\Pagebuilder;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Feenstra\CMS\Pagebuilder\Filament\Resources\LocaleResource;
use Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource;
use Feenstra\CMS\Pagebuilder\Filament\Resources\TranslationResource;

class PagebuilderPlugin implements Plugin {
    public function getId(): string {
        return 'tjall/laravel-pagebuilder';
    }

    public function register(Panel $panel): void {
        $panel
            ->resources([
                PageResource::class,
                TranslationResource::class,
                LocaleResource::class
            ]);
    }

    public function boot(Panel $panel): void {
        //
    }
}
