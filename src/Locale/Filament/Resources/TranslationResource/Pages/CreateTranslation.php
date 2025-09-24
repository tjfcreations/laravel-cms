<?php

namespace FeenstraDigital\LaravelCMS\Locale\Filament\Resources\TranslationResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Resources\TranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTranslation extends CreateRecord
{
    protected static string $resource = TranslationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array 
    {
        return TranslationResource::unpackData($data);
    }
}
