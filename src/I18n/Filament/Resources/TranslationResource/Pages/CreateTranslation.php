<?php

namespace Feenstra\CMS\I18n\Filament\Resources\TranslationResource\Pages;

use Feenstra\CMS\I18n\Filament\Resources\TranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTranslation extends CreateRecord
{
    protected static string $resource = TranslationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return TranslationResource::handleSave($data);
    }
}
