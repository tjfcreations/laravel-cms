<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Actions\TranslateAction;
use FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource;
use FeenstraDigital\LaravelCMS\Pagebuilder\Jobs\RecacheRoutes;
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
