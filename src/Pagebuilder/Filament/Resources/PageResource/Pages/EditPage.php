<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use FeenstraDigital\LaravelCMS\Locale\Filament\Actions\TranslateAction;
use FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
}
