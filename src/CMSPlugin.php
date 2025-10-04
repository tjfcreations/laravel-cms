<?php

namespace Feenstra\CMS;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CMSPlugin implements Plugin {
    public function getId(): string {
        return 'tjall/laravel-cms';
    }

    public function register(Panel $panel): void {
        $panel
            ->resources([
                I18n\Filament\Resources\LocaleResource::class,
                I18n\Filament\Resources\TranslationResource::class,
                
                Pagebuilder\Filament\Resources\PageResource::class
            ]);
    }

    public function boot(Panel $panel): void {
        //
    }
}
