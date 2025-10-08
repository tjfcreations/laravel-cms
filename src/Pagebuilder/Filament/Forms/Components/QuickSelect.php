<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Forms\Components;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Concerns;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;
use Closure;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Hidden;

class QuickSelect extends Grid {
    protected ?string $dateAttribute = null;

    protected ?string $recordLabel = null;

    protected ?string $model_ = null;

    public function setUp(): void {
        parent::setUp();

        $this
            ->columns(2)
            ->schema([
                Select::make('view')
                    ->label('Weergave')
                    ->options(function () {
                        $options = [
                            'all'      => 'Alle ' . $this->label,
                            'recent'   => 'Recente ' . $this->label,
                            'selected' => 'Geselecteerde ' . $this->label,
                        ];

                        if (!$this->dateAttribute) {
                            unset($options['recent']);
                        }

                        return $options;
                    })
                    ->default('selected')
                    ->live()
                    ->required()
                    ->selectablePlaceholder(false),
                Select::make('record_ids')
                    ->label(fn() => 'Kies ' . $this->label)
                    ->visible(fn(Get $get) => $get('view') === 'selected')
                    ->options(function () {
                        $query = $this->model_::query();

                        $table = (new $this->model_())->getTable();
                        if (Schema::hasColumn($table, 'created_at')) {
                            $query->orderBy('created_at', 'desc');
                        }

                        return $query
                            ->get()
                            ->map(function ($record) {
                                return [
                                    'label' => method_exists($record, 'getLabel') ? $record->getLabel() : $record->id,
                                    'id' => $record->id
                                ];
                            })
                            ->pluck('label', 'id');
                    })
                    ->placeholder('Typ om te zoeken...')
                    ->noSearchResultsMessage(fn() => "Geen {$this->label} gevonden voor deze zoekopdracht.")
                    ->multiple()
                    ->searchable()
                    ->required(),
                TextInput::make('limit')
                    ->label('Aantal')
                    ->visible(fn(Get $get) => $get('view') === 'recent')
                    ->numeric()
                    ->minValue(1)
                    ->default(3)
                    ->required()
            ]);
    }

    public function dateAttribute(string $dateAttribute = 'created_at'): static {
        $this->dateAttribute = is_string($dateAttribute) ? $dateAttribute : null;
        return $this;
    }

    public function recordLabel(string $recordLabel): static {
        $this->recordLabel = $recordLabel;
        return $this;
    }

    public function model_(string $model): static {
        $this->model_ = $model;
        return $this;
    }
}
