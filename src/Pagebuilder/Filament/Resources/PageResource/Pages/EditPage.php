<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use Feenstra\CMS\I18n\Filament\Actions\TranslateAction;
use Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource;
use Feenstra\CMS\Pagebuilder\Jobs\RecacheRoutes;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;

class EditPage extends EditRecord {
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array {
        return [
            TranslateAction::make()
                ->labels([
                    'title' => 'Titel'
                ]),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model {
        $originalPath = $record->getOriginal('path');

        $record = parent::handleRecordUpdate($record, $data);

        $currentPath = $record->path;

        // Only clear routes if path has changed
        if ($originalPath !== $currentPath) {
            // clear routes immediately to make the new route available
            Artisan::call('route:clear');

            RecacheRoutes::dispatchAfterResponse();
        }

        return $record;
    }
}
