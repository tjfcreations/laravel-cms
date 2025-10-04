<?php

namespace Feenstra\CMS\Locale\Filament\Resources;

use Feenstra\CMS\Locale\Filament\Actions\TranslateAction;
use Feenstra\CMS\Locale\Filament\Resources\TranslationResource\Pages;
use Feenstra\CMS\Locale\Models\Translation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Feenstra\CMS\Locale\Models\Locale;
use Feenstra\CMS\Locale\Registry;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Tabs;
use Filament\Support\Colors\Color;
use Filament\Forms\Set;

class TranslationResource extends Resource
{
    protected static ?string $slug = 'fd-cms-translations';

    protected static ?string $model = Translation::class;

    protected static ?string $navigationGroup = 'Vertalen';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $label = 'vertaling';
    protected static ?string $pluralLabel = 'vertalingen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Sleutel')
                    ->placeholder('title')
                    ->columnSpanFull()
                    ->required(),
                self::getTabs()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereNull('record_id'))
            ->columns([
                TextColumn::make('key')
                    ->label('Sleutel/attribuut')
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

    protected static function getTabs() {
        return Tabs::make()
            ->tabs(function() {
                return Locale::all()->map(function($locale) {
                    return self::getTab($locale);
                })->toArray();
            })
            ->columnSpanFull();
    }

    protected static function getTab(Locale $locale) {
        return Tabs\Tab::make($locale->name)
            ->schema(function(Translation $record) use($locale) {
                return [
                    TranslateAction::makeInput("translations.{$locale->code}.value", $record, $locale, false)
                        ->label("Vertaling ({$locale->name})")
                ];
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranslations::route('/'),
            'create' => Pages\CreateTranslation::route('/create'),
            'edit' => Pages\EditTranslation::route('/{record}/edit'),
        ];
    }

    /**
     * Custom method, called from EditTranslation and CreateTranslation pages.
     */
    public static function handleSave(array $data) {
        $translation = Translation::get($data['key']);

        // save translations
        foreach($data['translations'] as $locale => $data) {
            $value = $data['value'];

            // ignore if translation was not changed
            if($translation->translate($locale) === $value) continue;
            $translation->set($locale, $value, 'user', false);
        }

        $translation->save();
        
        $translation->updateMachineTranslations();

        return $translation;
    }
}
