<?php

namespace Feenstra\CMS\I18n\Filament\Resources\LocaleResource\Pages;

use Feenstra\CMS\I18n\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\Pagebuilder\Jobs\RecacheRoutes;

class CreateLocale extends CreateRecord {
    protected static string $resource = LocaleResource::class;

    protected function handleRecordCreation(array $data): Model {
        $record = parent::handleRecordCreation($data);

        if ($record->is_default) {
            $record->setAsDefault();
        }

        // recache because of new hreflang
        RecacheRoutes::dispatchAfterResponse();

        return $record;
    }
}
