<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use Feenstra\CMS\Locale\Filament\Actions\TranslateAction;
use Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource;
use Feenstra\CMS\Pagebuilder\Jobs\RecacheRoutes;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TranslateAction::make()
                ->labels([
                    'title' => 'Titel'
                ]),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave() {
        RecacheRoutes::dispatch();
    }
}
