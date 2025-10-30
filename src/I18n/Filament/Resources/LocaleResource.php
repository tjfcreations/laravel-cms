<?php

namespace Feenstra\CMS\I18n\Filament\Resources;

use Feenstra\CMS\I18n\Filament\Resources\LocaleResource\Pages;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Registry;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocaleResource extends Resource {
    protected static ?string $slug = 'fd-cms-locales';

    protected static ?string $model = Locale::class;

    protected static ?string $navigationGroup = 'Vertalen';
    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $label = 'taal';
    protected static ?string $pluralLabel = 'talen';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Naam')
                    ->helperText('Deze naam wordt getoond in het beheerpaneel.')
                    ->placeholder('Engels')
                    ->required(),
                Forms\Components\TextInput::make('native_name')
                    ->label('Vertaalde naam')
                    ->helperText('Deze naam wordt getoond op de website zelf.')
                    ->placeholder('English')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label('Taalcode')
                    ->placeholder('nl, en, de, fr, fr-CA...')
                    ->unique(ignoreRecord: true)
                    ->helperText('Deze taalcode wordt gebruikt voor automatische vertalingen.')
                    ->hintAction(Actions\Action::make('open_docs')
                        ->label('Bekijk taalcodes')
                        ->icon('heroicon-s-arrow-top-right-on-square')
                        ->url('https://docs.cloud.google.com/translate/docs/languages')
                        ->openUrlInNewTab())
                    ->required(),
                Forms\Components\TextInput::make('hreflang')
                    ->label('Hreflang')
                    ->placeholder('nl, en, de, fr, fr-CA...')
                    ->unique(ignoreRecord: true)
                    ->helperText('De hreflang wordt gebruikt als voorvoegsel in de URL.')
                    ->required(),
                Forms\Components\FileUpload::make('flag_path')
                    ->directory('uploads/locale-flags')
                    ->label('Vlag')
                    ->image()
                    ->columnSpan(1),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\ToggleButtons::make('is_default')
                            ->label('Standaardtaal')
                            ->boolean()
                            ->inline()
                            ->default(false)
                            ->grouped()
                            ->required()
                            ->live(),
                        Forms\Components\ToggleButtons::make('is_machine_translatable')
                            ->label('Vertaal automatisch')
                            ->boolean()
                            ->inline()
                            ->default(false)
                            ->grouped()
                            ->required()
                    ])
                    ->columnSpan(1)
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam'),
                TextColumn::make('native_name')
                    ->label('Vertaalde naam'),
                TextColumn::make('code')
                    ->label('Taalcode'),
                IconColumn::make('is_default')
                    ->label('Standaardtaal')
                    ->boolean(),
                IconColumn::make('is_machine_translatable')
                    ->label('Vertaal automatisch')
                    ->boolean()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListLocales::route('/'),
            'create' => Pages\CreateLocale::route('/create'),
            'edit' => Pages\EditLocale::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool {
        return Registry::isEnabled();
    }
}
