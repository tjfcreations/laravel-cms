<?php
namespace Feenstra\CMS\Media\Traits;

use Feenstra\CMS\Media\Filament\Forms\Components\MediaGalleryEditorRepeater;

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
