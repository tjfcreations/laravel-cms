<?php

namespace Feenstra\CMS\I18n\Filament\Resources;

use Feenstra\CMS\I18n\Filament\Actions\TranslateAction;
use Feenstra\CMS\I18n\Filament\Resources\TranslationResource\Pages;
use Feenstra\CMS\I18n\Filament\Support\TranslationsForm;
use Feenstra\CMS\I18n\Models\Translation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Registry;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Facades\Auth;

class TranslationResource extends Resource {
    protected static ?string $slug = 'fd-cms-translations';

    protected static ?string $model = Translation::class;

    protected static ?string $navigationGroup = 'Vertalen';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $label = 'vertaling';
    protected static ?string $pluralLabel = 'vertalingen';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Sleutel')
                    ->placeholder('title')
                    ->columnSpanFull()
                    ->required(),
                TranslationsForm::makeTabs([self::class, 'makeTab'], Locale::allWithDefaultFirst())
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            // only show global translations in the list
            ->modifyQueryUsing(fn($query) => $query->whereNull('record_id'))
            ->columns([
                TextColumn::make('key')
                    ->label('Sleutel')
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
            'index' => Pages\ListTranslations::route('/'),
            'create' => Pages\CreateTranslation::route('/create'),
            'edit' => Pages\EditTranslation::route('/{record}/edit'),
        ];
    }

    /**
     * Custom method, called from EditTranslation and CreateTranslation pages.
     */
    public static function handleSave(array $data, ?Translation $translation = null) {
        $translation = $translation ?? Translation::get($data['key']);

        foreach ($data['translations'] as $localeCode => $data) {
            $value = $data['value'];

            // ignore if translation was not changed
            if ($translation->getValue($localeCode) === $value) continue;
            $translation->set($localeCode, $value, Auth::user(), false);
        }

        $translation->save();

        if (app()->isLocal()) {
            $translation->updateMachineTranslations();
        } else {
            $translation->updateMachineTranslationsAsync();
        }

        return $translation;
    }

    public static function makeTab(Locale $locale) {
        return Forms\Components\Tabs\Tab::make($locale->name)
            ->schema(function (?Translation $record) use ($locale) {
                return [
                    TranslationsForm::makeValueInput($locale, "translations.{$locale->code}.value", false, $record)
                        ->label("Vertaling ({$locale->name})")
                ];
            });
    }

    public static function shouldRegisterNavigation(): bool {
        return Registry::isEnabled();
    }
}
