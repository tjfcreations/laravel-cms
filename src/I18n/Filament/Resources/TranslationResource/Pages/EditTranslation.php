<?php

namespace Feenstra\CMS\I18n\Filament\Resources\TranslationResource\Pages;

use Feenstra\CMS\I18n\Filament\Resources\TranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTranslation extends EditRecord
{
    protected static string $resource = TranslationResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return TranslationResource::handleSave($data, $record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
