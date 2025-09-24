<?php

namespace FeenstraDigital\LaravelCMS\Media\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;
use Filament\Support\Facades\FilamentIcon;
use FeenstraDigital\LaravelCMS\Media\Models\MediaItem;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaGalleryEditorFileUpload extends FileUpload {
    protected string $view = 'media-gallery::forms.components.media-gallery-editor-file-upload';

    protected function setUp(): void {
        parent::setUp();

        $this
            ->multiple()
            ->hiddenLabel()
            ->columnSpanFull()
            ->image();
    }
}
