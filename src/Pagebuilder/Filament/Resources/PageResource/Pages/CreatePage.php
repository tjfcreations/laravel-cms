<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Artisan;
use FeenstraDigital\LaravelCMS\Pagebuilder\Jobs\RecacheRoutes;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function afterSave() {
        RecacheRoutes::dispatch();
    }
}
