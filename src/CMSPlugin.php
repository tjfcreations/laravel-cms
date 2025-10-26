<?php

namespace Feenstra\CMS;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

class CMSPlugin implements Plugin {
    public function getId(): string {
        return 'tjall/laravel-cms';
    }

    public function register(Panel $panel): void {
        $panel
            // ->plugin(new FilamentShieldPlugin())
            ->pages([
                Common\Filament\Pages\LogViewer::class,
            ])
            ->resources([
                I18n\Filament\Resources\LocaleResource::class,
                I18n\Filament\Resources\TranslationResource::class,

                Pagebuilder\Filament\Resources\MenuResource::class,
                Pagebuilder\Filament\Resources\PageResource::class
            ]);
    }

    public function boot(Panel $panel): void {
        // hide menu repeater content
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn(): string => '<style>.fd-cms-repeater-no-content .fi-fo-repeater-item-content { display: none; }</style>',
        );
    }
}
