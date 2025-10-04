<?php
namespace Feenstra\CMS\I18n\Filament\Support;

use Filament\Support\Colors\Color;
use Filament\Forms\Components\Component;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Models\Translation;
use Filament\Forms;
use Filament\Forms\Set;

class TranslationsForm {   
    public static function makeValueInput(Locale $locale, string $name, bool $isRich = false, ?Translation $translation = null) {
        if($isRich) {
            $input = Forms\Components\RichEditor::make($name);
        } else {
            $input = Forms\Components\TextInput::make($name)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('reset')
                        ->icon('heroicon-m-trash')
                        ->color(Color::Red)
                        ->action(function (Set $set, $state) use($name) {
                            $set($name, null);
                        })
                );
        }

        // show whether the value is machine generated
        if($translation && $translation->isMachineTranslation($locale->code)) {
            $input
                ->hint('Automatisch gegenereerd')
                ->hintIcon('heroicon-s-bug-ant', tooltip: 'Deze vertaling is automatisch gegenereerd.')
                ->hintColor(Color::Blue);
        }

        return $input;
    }

    public static function makeCustomKeyValueEditor(Locale $locale): Forms\Components\KeyValue {
        return Forms\Components\KeyValue::make("_custom.{$locale->code}")
            ->label("Lokale vertalingen ({$locale->name})")
            ->keyLabel('Vertaalsleutel')
            ->valueLabel('Vertaling')
            ->hintColor('primary')
            ->hintIcon(
                'heroicon-m-question-mark-circle', 
                tooltip: str('Met lokale vertalingen kun je makkelijk je pagina-inhoud vertalen. Gebruik [translate vertaalsleutel] in je pagina-inhoud om een lokale vertaling te tonen.'));
    }

    public static function getTabs() {
        return Forms\Components\Tabs::make()
            ->tabs(function() {
                return Locale::all()->map(function($locale) {
                    return self::getTab($locale);
                })->toArray();
            })
            ->columnSpanFull();
    }

    public static function getTab(Locale $locale) {
        return Forms\Components\Tabs\Tab::make($locale->name)
            ->schema(function(?Translation $record) use($locale) {
                return [
                    self::makeValueInput($locale, "translations.{$locale->code}.value", false, $record)
                        ->label("Vertaling ({$locale->name})")
                ];
            });
    }
}