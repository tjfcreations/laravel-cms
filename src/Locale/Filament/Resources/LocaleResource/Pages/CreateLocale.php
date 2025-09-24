<?php

namespace FeenstraDigital\LaravelCMS\Locale\Filament\Resources\LocaleResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLocale extends CreateRecord
{
    protected static string $resource = LocaleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        if($record->is_default) {
            $record->setAsDefault();
        }

        return $record;
    }
}
