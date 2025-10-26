<?php

namespace Feenstra\CMS\Common\Filament\Pages;

use Filament\Pages\Page;
use Opcodes\LogViewer\Http\Controllers\IndexController;

class LogViewer extends Page {
    protected static ?string $navigationGroup = 'Beheer';
    protected static ?string $navigationLabel = 'Logs';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'fd-cms::common.filament.pages.log-viewer';
}
