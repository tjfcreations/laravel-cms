<?php
namespace FeenstraDigital\LaravelCMS\Media\Traits;

use FeenstraDigital\LaravelCMS\Media\Filament\Forms\Components\MediaGalleryEditorRepeater;

trait HasMediaGallerySettings {
    public function saveMediaGallerySettings(): void
    {
        foreach ($this->form->getFlatComponents() as $component) {
            if ($component instanceof MediaGalleryEditorRepeater) {
                $component->handleSave();
            }
        }
    }
}
