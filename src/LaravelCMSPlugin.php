<?php

namespace Feenstra\CMS;

use Filament\Contracts\Plugin;
use Filament\Panel;

class LaravelCMSPlugin implements Plugin {
    public function getId(): string {
        return 'tjall/laravel-cms';
    }

    public function register(Panel $panel): void {
        $panel
            ->resources([
                Locale\Filament\Resources\LocaleResource::class,
                Locale\Filament\Resources\TranslationResource::class,
                
                Pagebuilder\Filament\Resources\PageResource::class
            ]);
    }

    public function boot(Panel $panel): void {
        //
    }
}
