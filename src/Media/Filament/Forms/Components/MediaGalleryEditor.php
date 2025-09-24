<?php

namespace FeenstraDigital\LaravelCMS\Media\Filament\Forms\Components;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\Concerns;
use Closure;

class MediaGalleryEditor extends Grid {
    use Concerns\HasHelperText;
    use Concerns\HasLabel;

    public ?string $collectionName = null;
    
    public ?MediaGalleryEditorRepeater $repeater = null;
    public ?MediaGalleryEditorFileUpload $fileUpload = null;

    public static function make(array | int | string | null $collectionName = 'default'): static
    {
        $static = app(static::class);

        $static->collectionName = $collectionName;
        $static->configure();

        return $static;
    }
    
    protected function setUp(): void {
        parent::setUp();
        
        $prefix = "_media-gallery-editor-{$this->collectionName}";

        $this->repeater = MediaGalleryEditorRepeater::make($prefix.'-repeater')
            ->parent($this)
            ->label('Galerij')
            ->reorderable()
            ->extraFieldWrapperAttributes(function (mixed $state) {
                return [
                    'style' => is_array($state) && count($state) 
                        ? 'margin-bottom: -0.5rem;' 
                        : 'margin-bottom: -1.5rem;'
                ];
            });

        $this->fileUpload = MediaGalleryEditorFileUpload::make($prefix.'-file-upload')
            ->label('Media uploaden')
            ->hiddenLabel();

        $this
            ->columns(1)
            ->schema([
                $this->repeater,
                $this->fileUpload
            ]);
    }

    public function hiddenLabel(bool | Closure $condition = true): static {
        $this->repeater->hiddenLabel($condition);
        return $this;
    }

    public function label(string | Htmlable | Closure | null $label): static {
        $this->repeater->label($label);
        return $this;
    }

    public function helperText(string | Htmlable | Closure | null $text): static
    {
        $this->repeater->helperText($text);
        return $this;
    }
}
