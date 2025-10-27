<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Artisan;
use Feenstra\CMS\Pagebuilder\Jobs\RecacheRoutes;

class CreatePage extends CreateRecord {
    protected static string $resource = PageResource::class;

    protected function afterCreate() {
        RecacheRoutes::dispatch();
    }
}
