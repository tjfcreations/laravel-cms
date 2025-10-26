<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasPlaceholder;

class Link extends Field {
    use HasPlaceholder;

    protected string $view = 'filament-forms::components.grid';

    protected bool|Closure $isTelEnabled = false;
    protected bool|Closure $isExternalUrlEnabled = false;
    protected bool|Closure $isEmailEnabled = false;
    protected bool|Closure $isWithoutLabel = false;

    protected function setUp(): void {
        parent::setUp();

        $this
            ->schema(function () {
                $typeOptions = $this->getTypeOptions();

                return [
                    Forms\Components\TextInput::make('label')
                        ->label('Label')
                        ->placeholder($this->getPlaceholder() ?? 'Nieuwe link')
                        ->required()
                        ->live()
                        ->hidden($this->evaluate($this->isWithoutLabel)),

                    Forms\Components\Select::make('type')
                        ->label('Linktype')
                        ->options($typeOptions)
                        ->live()
                        ->default('page')
                        ->required()
                        ->selectablePlaceholder(false),

                    Forms\Components\Select::make('page_id')
                        ->label('Pagina')
                        ->options(Page::whereNot('type', 'error')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->visible(fn(Forms\Get $get) => $get('type') === 'page')
                        ->columnSpan(1),

                    Forms\Components\Select::make('page_record_id')
                        ->label(function (Forms\Get $get) {
                            $pageId = $get('page_id');
                            $page = Page::find($pageId);
                            if (!$page || !@class_exists($page->model) || !method_exists($page->model, 'getTemplatableLabel')) return;

                            $instance = (new $page->model());
                            return 'Kies een ' . ($instance->getTemplatableLabel() ?? 'record');
                        })
                        ->required()
                        ->options(function (Forms\Get $get, mixed $record) {
                            $pageId = $get('page_id');
                            if (!$pageId) return [];

                            $page = Page::find($pageId);
                            if (!$page || !$page->isTemplate()) return [];

                            $model = $page->model;
                            if (!$model || !class_exists($model)) return [];

                            $records = $model::all();
                            $options = $records
                                ->map(function ($record) {
                                    return [
                                        'label' => method_exists($record, 'getLabel') ? $record->getLabel() : $record->id,
                                        'id' => $record->id
                                    ];
                                })
                                ->sortBy('label')
                                ->pluck('label', 'id');

                            // if the current page has the same model type as the target page,
                            // allow linking to the same record
                            if ($page->model === $record->model) {
                                $options->prepend('(zelfde als deze pagina)', 'CURRENT_PAGE_RECORD_ID');
                            }

                            return $options;
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
                ];
            })
            ->columns(2)
            ->columnSpanFull();
    }

    protected function getTypeOptions(): array {
        $options = ['page' => 'Pagina'];

        if ($this->evaluate($this->isExternalUrlEnabled)) {
            $options['external_url'] = 'Externe URL';
        }

        if ($this->evaluate($this->isEmailEnabled)) {
            $options['mailto'] = 'E-mailadres';
        }

        if ($this->evaluate($this->isTelEnabled)) {
            $options['tel'] = 'Telefoon';
        }

        return $options;
    }

    public function tel(bool|Closure $condition = true): self {
        $this->isTelEnabled = $condition;
        return $this;
    }

    public function externalUrl(bool|Closure $condition = true): self {
        $this->isExternalUrlEnabled = $condition;
        return $this;
    }

    public function email(bool|Closure $condition = true): self {
        $this->isEmailEnabled = $condition;
        return $this;
    }

    public function withoutLabel(bool|Closure $condition = true): self {
        $this->isWithoutLabel = $condition;
        return $this;
    }

    public static function make(string $name): static {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }
}
