<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources\MenuResource\Pages;

use Feenstra\CMS\Pagebuilder\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Feenstra\CMS\I18n\Filament\Actions\TranslateAction;

class EditMenu extends EditRecord {
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array {
        return [
            TranslateAction::make()
                ->labels([
                    'name' => 'Naam'
                ]),
            Actions\DeleteAction::make(),
        ];
    }
}
