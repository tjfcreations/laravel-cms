<?php

namespace FeenstraDigital\LaravelCMS\Locale\Filament\Resources\LocaleResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLocale extends EditRecord
{
    protected static string $resource = LocaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        if($record->is_default) {
            $record->setAsDefault();
        }

        return $record;
    }
}
