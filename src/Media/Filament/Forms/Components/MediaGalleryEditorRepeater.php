<?php

namespace Feenstra\CMS\Media\Filament\Forms\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Component;
use Illuminate\Support\Facades\Blade;
use Feenstra\CMS\Media\Models\MediaItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Filament\Pages\SettingsPage;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\Settings;
use Feenstra\CMS\Media\SettingsCasts\MediaGalleryCast;
use Feenstra\CMS\Media\Support\SettingsPropertyMediaContainer;

class MediaGalleryEditorRepeater extends Repeater {
    protected ?MediaGalleryEditor $parent = null;

    protected function setUp(): void {
        parent::setUp();

        $this
            ->schema([
                $this->renderItemThumbnail('sm', null, true)
            ])
            ->extraItemActions([
                $this->getEditMetaAction(),
            ])
            ->columnSpanFull()
            ->grid([
                'default' => 2,
                'md' => 3,
                'lg' => 4,
                'xl' => 5,
                '2xl' => 6,
            ])
            ->addAction(function (Action $action) {
                return $action->hidden();
            })
            ->registerActions([
                fn(MediaGalleryEditorRepeater $component): Action => $component->getUploadStartAction(),
                fn(MediaGalleryEditorRepeater $component): Action => $component->getUploadSuccessAction(),
                fn(MediaGalleryEditorRepeater $component): Action => $component->getDeleteAction()
            ])
            ->relationship('media')
            ->afterStateHydrated(static function (MediaGalleryEditorRepeater $component, mixed $state): void {
                if(!is_array($state)) return;
                
                $items = [];

                $media_items = $component->getMediaItemsFromState($state);
                foreach ($media_items as $media_item) {
                    $items["item-{$media_item->id}"] = [
                        'media_item' => $media_item,
                        'is_uploading' => false,
                        'meta' => $media_item->getMeta(),
                        'order_column' => $media_item->order_column
                    ];
                }

                $component->state($items);
                $component->callAfterStateUpdated();
            })
            ->saveRelationshipsUsing(function(MediaGalleryEditorRepeater $component) {
                return $component->handleSave();
            });
    }

    public function parent(MediaGalleryEditor $parent): static {
        $this->parent = $parent;
        return $this;
    }

    public function getMediaItemsFromState(array $state) {
        return collect($state)
            ->filter(fn($data) => isset($data['id']))
            ->map(fn($data) => MediaItem::find($data['id']))
            ->sortBy('order_column');
    }

    public function getUploadStartAction() {
        return Action::make('onUploadStart')
            ->action(function (array $arguments, MediaGalleryEditorRepeater $component) {
                $items = $component->getState();

                foreach ($arguments['uploadIds'] as $upload_id) {
                    $items["upload-{$upload_id}"] = [
                        'upload_id' => $upload_id,
                        'is_uploading' => true,
                        'meta' => []
                    ];
                }

                $component->state($items);

                $component->callAfterStateUpdated();
            });
    }

    public function getUploadSuccessAction() {
        return Action::make('onUploadSuccess')
            ->action(function (array $arguments, MediaGalleryEditorRepeater $component) {
                $items = $component->getState();

                foreach ($arguments['uploadIds'] as $upload_id) {
                    $items["upload-{$upload_id}"]['is_uploading'] = false;
                }

                $component->state($items);

                $component->callAfterStateUpdated();
            });
    }
    
    public function handleSave() {
        $record = $this->getRecord();
        $items = $this->getState();
        $data = $this->getLivewire()->data;

        
        // merge all uploaded files into a single array
        $files = [];
        foreach($data as $key => $file) {
            if(!str_starts_with($key, '_media-gallery-editor-') || !str_contains($key, '#upload-')) continue;

            $item_id = explode('#', $key)[1];
            $files[$item_id] = $file;
        }
        
        // array for storing media items that should not be deleted
        $keep_media_items = [];
        foreach($files as $item_id => $file) {
            // skip if the item was uploaded, but then removed
            if(!isset($items[$item_id])) continue;

            // check if the media item already exists
            if(!isset($items[$item_id]['media_item'])) {
                // save the media item
                try {
                    if(method_exists($record, 'addMedia')) {
                        // if the record has an addMedia method, use it
                        $items[$item_id]['media_item'] = $record
                            ->addMedia($file)
                            ->toMediaCollection($this->parent->collectionName);
                    } else {
                        // create a helper record to insert the media into
                        // the database
                        $mediaContainer = new SettingsPropertyMediaContainer();
                        $mediaContainer->id = $record->id;
                        $mediaContainer->exists = true;
                        
                        // update the model related to the media item
                        $mediaItem = $mediaContainer
                            ->addMedia($file)
                            ->toMediaCollection();
                        $mediaItem->save();

                        $items[$item_id]['media_item'] = $mediaItem;
                    }
                } catch(\Exception $e) {
                    throw $e;
                    continue;
                }
            }

            $keep_media_items[$items[$item_id]['media_item']?->id] = true;
        }

        // update media item meta and order
        $item_order = 1;
        foreach($items as $item_id => $data) {
            if(!isset($data['media_item'])) continue;

            $media_item = $data['media_item'];
            $media_item->setMeta($data['meta']);
            $media_item->order_column = $item_order++;
            $media_item->save();

            $keep_media_items[$media_item->id] = true;
        }

        // delete media items that the user deleted
        $existing_media_items = $this->getRelationship()->getQuery()->get()->all();
        foreach($existing_media_items as $media_item) {
            if(isset($keep_media_items[$media_item->id])) continue;
            $media_item->delete();
        }

        // Update settings value
        $settingsClass = $this->getRelatedSettingsClass();
        if($settingsClass) {
            $settings = app($settingsClass);

            $name = $this->parent->collectionName;
            $ids = array_keys($keep_media_items);
            $settings->$name = (new MediaGalleryCast())->get($ids);
        }

        $this->state($items);

        $this->callAfterStateUpdated();
    }

    protected function renderItemThumbnail(string $conversion = 'sm', ?array $item = null, ?bool $progress = false): Placeholder {
        $placeholder = Placeholder::make('thumbnail')
            ->content(function (Component $component) use ($conversion, $item, $progress) {
                $html = Blade::render('<x-fd-cms::media.filament.forms.media-gallery-editor-repeater-item-thumbnail :component="$component" :conversion="$conversion" :item="$item" :progress="$progress" />', [
                    'conversion' => $conversion,
                    'component' => $component,
                    'item' => $item,
                    'progress' => $progress
                ]);
                return new HtmlString($html);     
            })
            ->hiddenLabel()
            ->columnSpanFull();

        return $placeholder;
    }

    protected function getItemMeta(string $item_id) {
        return $this->getState()[$item_id]['meta'];
    }

    protected function setItemMeta(string $item_id, array $meta) {
        $items = $this->getState();
        $items[$item_id]['meta'] = $meta;
        $this->state($items);
    }

    public function getEditMetaAction() {
        return Action::make('editMeta')
            ->label(__('filament-actions::edit.single.label'))
            ->icon('heroicon-m-pencil-square')
            ->form(function (array $arguments, MediaGalleryEditorRepeater $component) {
                $item = $this->getState()[$arguments['item']];

                $is_multi_locale = false;

                return [
                    $component->renderItemThumbnail('md', $item)
                        ->extraAttributes([
                            'class' => 'w-full aspect-square object-cover',
                            'style' => 'max-width: 10rem;'
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('author')
                                ->label('Auteur')
                                ->placeholder('Jan de Vries'),
                            TextInput::make('date')
                                ->label('Gemaakt op')
                                ->type('date'),
                            $is_multi_locale
                                ? null
                                : Textarea::make('description')
                                ->label('Beschrijving')
                                ->rows(4)
                                ->columnSpanFull(),
                        ])
                ];
            })
            ->modalHeading('Media bewerken')
            ->modalSubmitActionLabel('Sluiten')
            ->fillForm(function (array $arguments, MediaGalleryEditorRepeater $component) {
                return $this->getItemMeta($arguments['item']);
            })
            ->action(function (array $arguments, MediaGalleryEditorRepeater $component, array $data) {
                return $this->setItemMeta($arguments['item'], $data);
            });
    }

    public function getModelInstance(): ?Model {
        return $this->getRelatedSettingsProperty() ?? parent::getModelInstance();
    }

    public function getRecord(): ?Model {
        return $this->getRelatedSettingsProperty() ?? parent::getRecord();
    }

    public function getRelationship(): HasOneOrMany | BelongsToMany | null {
        $settingsProperty = $this->getRelatedSettingsProperty();
        
        if(isset($settingsProperty)) {
            $mediaContainer = new SettingsPropertyMediaContainer();
            $mediaContainer->id = $settingsProperty->id;
            return $mediaContainer->morphMany(MediaItem::class, 'model');
        }

        return parent::getRelationship();
    }

    public function getRelatedSettingsClass(): string | null {
        $livewire = $this->getLivewire();
        if($livewire instanceof SettingsPage) {
            $livewire = $this->getLivewire();
            $settingsClass = $livewire::class::getSettings();
            return $settingsClass;
        }

        return null;
    }

    public function getRelatedSettingsProperty(): SettingsProperty | null {
        $settingsClass = $this->getRelatedSettingsClass();
        if(!$settingsClass) return null;

        $group = $settingsClass::group();
        $name = $this->parent->collectionName;
        
        $setting = SettingsProperty::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first();

        if(!isset($setting)) {
            throw new \Exception("No setting property called '$name' in group '$group'. Please ensure the Filament component is named correctly.");
        }

        return $setting;
    }
}
