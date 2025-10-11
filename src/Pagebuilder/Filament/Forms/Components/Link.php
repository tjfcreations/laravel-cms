<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Filament\Forms\Components\Grid;
use Filament\Forms;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Filament\Forms\Components\Field;

class Link extends Field {
    protected string $view = 'filament-forms::components.grid';

    protected function setUp(): void {
        parent::setUp();

        $this
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

                Forms\Components\Select::make('page_id')
                    ->label('Pagina')
                    ->options(Page::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->visible(fn(Forms\Get $get) => $get('type') === 'page')
                    ->columnSpan(1),

                Forms\Components\Select::make('page_record_id')
                    ->label('Record')
                    ->options(function (Forms\Get $get) {
                        $pageId = $get('page_id');
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
                        $pageId = $get('page_id');
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
            ])
            ->columns(2)
            ->columnSpanFull();
    }

    public static function make(string $name): static {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }
}
