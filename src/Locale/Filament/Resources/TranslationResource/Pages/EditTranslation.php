<?php

namespace FeenstraDigital\LaravelCMS\Locale\Filament\Resources\TranslationResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Resources\TranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTranslation extends EditRecord
{
    protected static string $resource = TranslationResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return TranslationResource::packData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TranslationResource::unpackData($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
