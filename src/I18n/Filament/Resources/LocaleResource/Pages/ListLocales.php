<?php

namespace Feenstra\CMS\I18n\Filament\Resources\LocaleResource\Pages;

use Feenstra\CMS\I18n\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLocales extends ListRecords
{
    protected static string $resource = LocaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
