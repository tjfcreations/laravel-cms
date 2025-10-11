<?php

namespace Feenstra\CMS\Pagebuilder\Filament\Resources;

use Feenstra\CMS\Pagebuilder\Filament\Resources\PageResource\Pages;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
use Filament\Tables\Columns;
use Feenstra\CMS\Pagebuilder\Filament\Forms\Components\Pagebuilder;
use Feenstra\CMS\Pagebuilder\Filament\Forms\Components\Pageheader;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Feenstra\CMS\Pagebuilder\Enums\PageStatusEnum;
use Feenstra\CMS\Pagebuilder\Filament\Forms\Components\ButtonRepeater;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Feenstra\CMS\Pagebuilder\Registry;

class PageResource extends Resource {
    protected static ?string $slug = 'fd-cms-pages';

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?int $navigationSort = 999;

    protected static ?string $label = 'pagina';
    protected static ?string $pluralLabel = 'pagina\'s';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->helperText('Deze naam wordt getoond in het beheerpaneel.')
                    ->label('Naam')
                    ->placeholder('Nieuwe pagina')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('path')
                    ->helperText('Gebruik {slug} of {id} voor een template.')
                    ->placeholder('/nieuwe-pagina')
                    ->dehydrateStateUsing(fn($state) => '/' . trim(trim($state), '/'))
                    ->label('Pad')
                    ->hidden(fn(Get $get) => $get('type') === PageTypeEnum::Error->value),
                Forms\Components\ToggleButtons::make('status')
                    ->label('Status')
                    ->grouped()
                    ->options(PageStatusEnum::class)
                    ->default(PageStatusEnum::Published)
                    ->required()
                    ->columnSpanFull()
                    ->hidden(),
                Forms\Components\Hidden::make('options')
                    ->default([]),
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('pagebuilder')
                            ->label('Inhoud')
                            ->schema([
                                Pagebuilder::make('pagebuilder'),
                            ]),
                        // Tabs\Tab::make('pageheader')
                        //     ->label('Header')
                        //     ->schema([
                        //         Pageheader::make('header')
                        //     ]),
                        Tabs\Tab::make('header')
                            ->label('Header')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Forms\Components\Grid::make(1)
                                            ->schema([
                                                Forms\Components\TextInput::make('title')
                                                    ->label('Titel')
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('header_subtitle')
                                                    ->label('Subtitel')
                                                    ->rows(3),
                                                ButtonRepeater::make('buttons')
                                            ])
                                            ->columnSpan(1)
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                            ]),
                        Tabs\Tab::make('settings')
                            ->label('Instellingen')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Paginatype')
                                    ->options(PageTypeEnum::class)
                                    ->native(false)
                                    ->default(PageTypeEnum::Default)
                                    ->live()
                                    ->required()
                                    ->selectablePlaceholder(false),
                                Forms\Components\Select::make('model')
                                    ->label('Model')
                                    ->options(self::getModelOptions())
                                    ->native(false)
                                    ->required()
                                    ->placeholder('Kies een model...')
                                    ->visible(fn(Get $get) => self::isPageType(PageTypeEnum::Template, $get('type'))),
                                Forms\Components\Select::make('error_code')
                                    ->label('Foutcode')
                                    ->options([
                                        '4XX' => [
                                            400 => '400 - Bad Request',
                                            401 => '401 - Unauthorized',
                                            402 => '402 - Payment Required',
                                            403 => '403 - Forbidden',
                                            404 => '404 - Not Found',
                                        ],
                                        '5XX' => [
                                            500 => '500 - Internal Server Error',
                                            503 => '503 - Service Unavailable'
                                        ]
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->placeholder('Kies een foutcode...')
                                    ->visible(fn(Get $get) => self::isPageType(PageTypeEnum::Error, $get('type'))),
                            ]),
                    ]),
            ]);
    }

    public static function afterSave(Model $record): void {
        $record->updateRoute(true);
    }

    protected static function isPageType(PageTypeEnum $a, mixed $b): bool {
        if ($b instanceof PageTypeEnum) return $a === $b;
        return PageTypeEnum::tryFrom($b) == $a;
    }

    protected static function getModelOptions(): array {
        $options = [];
        foreach (Registry::models() as $model) {
            $options[$model::class] = class_basename($model);
        }

        return $options;
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->description(fn(Page $page): string => $page->type !== PageTypeEnum::Default ? $page->type->getLabel() : '')
                    ->sortable()
                    ->searchable(),
                Columns\TextColumn::make('path')
                    ->label('Pad')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('name', 'asc')
            ->defaultPaginationPageOption(25)
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->orderByRaw("CASE WHEN type = 'error' THEN 1 ELSE 0 END ASC")
                    ->orderBy('name', 'asc');
            });
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
