<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Filament\Forms\Components\Component;

class MenuItemRepeater extends Repeater {
    protected string $view = 'fd-cms::pagebuilder.filament.forms.components.menu-item-repeater';

    protected function getMaxDepth(): int {
        return config('fd-cms.pagebuilder.menu.max_depth', 3);
    }

    protected function setUp(): void {
        parent::setUp();

        $this
            ->addActionLabel('Item toevoegen')
            ->reorderableWithButtons()
            ->itemLabel(fn(array $state) => ($state['label'] ?? '(geen label)'))
            ->extraItemActions([
                $this->getItemAddChildAction(),
                $this->getItemEditAction(),
            ])
            ->schema([
                Forms\Components\Hidden::make('type')
                    ->default('page')
            ])
            ->moveUpAction(function (Action $action) {
                return $action
                    ->label('Naar links')
                    ->icon('heroicon-s-arrow-left')
                    ->action(function (array $arguments) {
                        $this->changeItemDepth($arguments, -1);
                    });
            })
            ->moveDownAction(function (Action $action) {
                return $action
                    ->label('Naar rechts')
                    ->icon('heroicon-s-arrow-right')
                    ->action(function (array $arguments) {
                        $this->changeItemDepth($arguments, 1);
                    });
            });
    }

    public function getAddAction(): Action {
        return parent::getAddAction()
            ->label('Item toevoegen')
            ->form(function (Form $form) {
                return $this->getEditForm($form);
            })
            ->modalSubmitActionLabel('Toevoegen')
            ->action(function (array $data, Component $component): void {
                $state = $component->getState();
                $newItem = array_merge($data, ['depth' => 0]);

                // Generate a unique key
                $newUuid = $component->generateUuid();

                // Add the new item
                $state[$newUuid] = $newItem;

                // Save the new repeater state
                $component->state($state);
            });
    }

    protected function getItemEditAction(): Action {
        return Action::make('edit')
            ->label('Bewerk')
            ->icon('heroicon-s-pencil-square')
            ->color('primary')
            ->modalSubmitActionLabel('Opslaan')
            ->fillForm(function (array $arguments): array {
                $state = $this->getState();
                return $state[$arguments['item']] ?? [];
            })
            ->form(function (Form $form) {
                return $this->getEditForm($form);
            })
            ->action(function (array $arguments, array $data): void {
                $state = $this->getState();
                $state[$arguments['item']] = array_merge($state[$arguments['item']] ?? [], $data);
                $this->state($state);
            });
    }

    protected function getItemAddChildAction(): Action {
        return Action::make('add_child')
            ->label('Sub-item toevoegen')
            ->icon('heroicon-s-plus')
            ->color('success')
            ->visible(fn(array $arguments): bool => $this->getItemDepth($arguments) < $this->getMaxDepth())
            ->form(fn(Form $form) => $this->getEditForm($form))
            ->modalSubmitActionLabel('Toevoegen')
            ->action(function (array $arguments, array $data, Component $component): void {
                $state = $component->getState();
                $currentDepth = $component->getItemDepth($arguments);
                $targetDepth = $currentDepth + 1;

                // Find the position to insert (after all children of current item)
                $keys = array_keys($state);
                $currentIndex = array_search($arguments['item'], $keys);
                $insertIndex = $currentIndex + 1;

                for ($i = $currentIndex + 1; $i < count($keys); $i++) {
                    $itemDepth = $component->getItemDepth(['item' => $keys[$i]]);
                    if ($itemDepth <= $currentDepth) {
                        break;
                    }
                    $insertIndex = $i + 1;
                }

                // Prepare the new item using the form data
                $newItem = array_merge($data, ['depth' => $targetDepth]);

                // Generate a unique key
                $newUuid = $component->generateUuid();

                // Insert the new item
                $newState = [];
                foreach ($keys as $index => $key) {
                    $newState[$key] = $state[$key];
                    if ($index + 1 === $insertIndex) {
                        $newState[$newUuid] = $newItem;
                    }
                }

                if ($insertIndex >= count($keys)) {
                    $newState[$newUuid] = $newItem;
                }

                // Save the new repeater state
                $component->state($newState);
            });
    }

    protected function getEditForm(Form $form) {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Label')
                    ->placeholder('Nieuw item')
                    ->required()
                    ->live(),

                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'page' => 'Pagina',
                        'external_url' => 'Externe URL',
                        'title' => 'Titel',
                        'mailto' => 'E-mailadres',
                        'tel' => 'Telefoon',
                    ])
                    ->live()
                    ->default('page')
                    ->required()
                    ->selectablePlaceholder(false),

                Forms\Components\Select::make('page')
                    ->label('Pagina')
                    ->options(Page::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->visible(fn(Forms\Get $get) => $get('type') === 'page')
                    ->columnSpanFull(),

                Forms\Components\Select::make('page_record')
                    ->label('Record')
                    ->options(function (Forms\Get $get) {
                        $pageId = $get('page');
                        if (!$pageId) return [];

                        $page = Page::find($pageId);
                        if (!$page || !$page->isTemplate()) return [];

                        $model = $page->model;
                        if (!$model || !class_exists($model)) return [];

                        $records = $model::all();
                        return $records
                            ->map(function ($record) {
                                return [
                                    'label' => method_exists($record, 'getLabel') ? $record->getLabel() : $record->id,
                                    'id' => $record->id
                                ];
                            })
                            ->sortBy('label')
                            ->pluck('label', 'id');
                    })
                    ->searchable()
                    ->visible(function (Forms\Get $get) {
                        $pageId = $get('page');
                        if (!$pageId) return false;

                        $page = Page::find($pageId);
                        return $page && $page->isTemplate();
                    }),

                Forms\Components\TextInput::make('external_url')
                    ->label('Externe URL')
                    ->url()
                    ->required()
                    ->visible(fn(Forms\Get $get) => $get('type') === 'external_url')
                    ->placeholder('https://example.com')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('mailto')
                    ->label('E-mailadres')
                    ->email()
                    ->required()
                    ->visible(fn(Forms\Get $get) => $get('type') === 'mailto')
                    ->placeholder('naam@example.com')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('tel')
                    ->label('Telefoonnummer')
                    ->tel()
                    ->required()
                    ->visible(fn(Forms\Get $get) => $get('type') === 'tel')
                    ->placeholder('06 12345678')
                    ->columnSpanFull(),
            ]);
    }

    public function changeItemDepth(array $arguments, int $changeBy = 1) {
        $state = $this->getState();
        $state[$arguments['item']]['depth'] = ($state[$arguments['item']]['depth'] ?? 0) + $changeBy;
        $this->state($state);
    }

    public function getItemDepth(array $arguments): int {
        $state = $this->getState();
        return $state[$arguments['item']]['depth'] ?? 0;
    }

    public function canMoveDown(int $itemDepth, ?int $previousItemDepth) {
        if ($itemDepth >= $this->getMaxDepth()) {
            return false;
        }

        if ($previousItemDepth === null) {
            return false;
        }

        return $itemDepth <= $previousItemDepth;
    }
}
