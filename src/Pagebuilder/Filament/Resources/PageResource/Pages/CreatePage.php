<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Artisan;
use Feenstra\CMS\Pagebuilder\Jobs\RecacheRoutes;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function afterSave() {
        RecacheRoutes::dispatch();
    }
}
