<?php

namespace Feenstra\CMS\Media\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaUpload extends Component {
    use WithFileUploads;

    public $files = [];
    
    public string $accept;
    public string $statePath;

    public function render() {
        return view('media-gallery::livewire.media-upload');
    }

    public function updatedFiles() {
        $this->dispatch('media-upload:success', [
            'files' => collect($this->files)->map(function($file) {
                return [
                    'clientName' => $file->getClientOriginalName(),
                    'name' => $file->getFilename(),
                    'size' => $file->getSize()
                ];
            })->toArray()
        ])->self();
    }
}
