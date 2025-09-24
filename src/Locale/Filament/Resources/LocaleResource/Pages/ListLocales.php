<?php

namespace FeenstraDigital\LaravelCMS\Locale\Filament\Resources\LocaleResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Resources\LocaleResource;
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
