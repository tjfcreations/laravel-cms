<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource\Pages;

use FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
