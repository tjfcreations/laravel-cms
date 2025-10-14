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
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->hintAction(
                        Forms\Components\Actions\Action::make('view_page')
                            ->label('Pagina bekijken')
                            ->icon('heroicon-s-arrow-top-right-on-square')
                            ->iconPosition('after')
                            ->url(fn($record) => $record?->localizedUrl())
                            ->openUrlInNewTab()
                            ->visible(fn($livewire) => $livewire instanceof EditRecord)
                    )
                    ->maxLength(255),
                Forms\Components\TextInput::make('path')
                    ->helperText('Gebruik {slug} of {id} voor een template.')
                    ->placeholder('/nieuwe-pagina')
                    ->dehydrateStateUsing(fn($state) => '/' . trim(trim($state), '/'))
                    ->label('Pad')
                    ->hidden(fn(Get $get) => $get('type') === PageTypeEnum::Error->value),
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
                                                Forms\Components\TextInput::make('options.header.title')
                                                    ->label('Titel')
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('options.header.subtitle')
                                                    ->label('Subtitel')
                                                    ->rows(3),
                                                ButtonRepeater::make('options.header.buttons')
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
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->helperText('De slug wordt intern gebruikt om naar deze pagina te verwijzen.')
                                    ->placeholder('Bijv. project.index, project.show, project.donate...')
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn($state) => Str::snake(Str::lower($state)))
                                    ->disabled(fn($livewire, $get) => self::isSlugInputDisabled($livewire, $get) && !$get('is_slug_editable'))
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('edit_slug')
                                            ->label('Bewerk')
                                            ->icon('heroicon-s-pencil')
                                            ->color('danger')
                                            ->visible(fn($livewire, $get) => self::isSlugInputDisabled($livewire, $get))
                                            ->requiresConfirmation()
                                            ->modalHeading('Slug bewerken')
                                            ->modalSubmitActionLabel('Ja, bewerken')
                                            ->modalDescription('Weet je zeker dat je de slug wilt bewerken? Dit kan bestaande links naar deze pagina breken.')
                                            ->action(function ($set) {
                                                $set('is_slug_editable', true);
                                            })
                                    ),

                                Forms\Components\Hidden::make('is_slug_editable')
                                    ->dehydrated(false)
                                    ->default(false),

                                Forms\Components\ToggleButtons::make('status')
                                    ->label('Status')
                                    ->grouped()
                                    ->options(PageStatusEnum::class)
                                    ->default(PageStatusEnum::Published)
                                    ->required(),

                                Forms\Components\Select::make('type')
                                    ->label('Paginatype')
                                    ->options(PageTypeEnum::class)
                                    ->native(false)
                                    ->default(PageTypeEnum::Generic)
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

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function isSlugInputDisabled($livewire, $get) {
        if ($livewire instanceof EditRecord) {
            return !empty($get('slug'));
        }

        return false;
    }

    protected static function isPageType(PageTypeEnum $a, mixed $b): bool {
        if ($b instanceof PageTypeEnum) return $a === $b;
        return PageTypeEnum::tryFrom($b) == $a;
    }

    protected static function getModelOptions(): array {
        $options = [];
        foreach (Registry::models() as $model) {
            $label = class_basename($model);

            if (method_exists($model, 'getTemplatableLabel')) {
                $label = (new $model())->getTemplatableLabel();
            }

            $options[$model::class] = ucfirst($label);
        }

        return $options;
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->description(fn(Page $page): string => $page->slug)
                    ->sortable()
                    ->searchable(),
                Columns\TextColumn::make('path')
                    ->label('Pad')
                    ->sortable()
                    ->searchable()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
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
