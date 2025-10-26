<?php

namespace Feenstra\CMS\I18n\Filament\Resources\LocaleResource\Pages;

use Feenstra\CMS\I18n\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\Pagebuilder\Jobs\RecacheRoutes;

class EditLocale extends EditRecord {
    protected static string $resource = LocaleResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model {
        $record = parent::handleRecordUpdate($record, $data);

        if ($record->is_default) {
            $record->setAsDefault();
        }

        // hreflang may have changed
        RecacheRoutes::dispatchAfterResponse();

        return $record;
    }
}
