<?php

namespace Feenstra\CMS\I18n\Filament\Support;

use Filament\Support\Colors\Color;
use Filament\Forms\Components\Component;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Models\Translation;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Support\Collection;

class TranslationsForm {
    public static function mutateFormComponent(Locale $locale, Component $component, ?Translation $translation = null): void {
        // show whether the value is machine generated
        if ($translation) {
            $formattedDate = $translation->updatedAt($locale->code)->format('d-m-Y \o\m H:i.');

            if ($translation->isMachineTranslation($locale->code)) {
                $component
                    ->hintIcon('heroicon-s-bolt', tooltip: 'Automatisch vertaald op ' . $formattedDate)
                    ->hintColor(Color::Purple);
            } elseif ($translation->isUserTranslation($locale->code)) {
                $component
                    ->hintIcon('heroicon-s-clock', tooltip: 'Handmatig aangepast op ' . $formattedDate);
            }
        }
    }


    public static function makeValueInput(Locale $locale, string $name, bool $isRich = false, ?Translation $translation = null) {
        if ($isRich) {
            $component = Forms\Components\RichEditor::make($name);
        } else {
            $component = Forms\Components\TextInput::make($name)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('reset')
                        ->icon('heroicon-m-trash')
                        ->color(Color::Red)
                        ->action(function (Set $set, $state) use ($name) {
                            $set($name, null);
                        })
                );
        }

        static::mutateFormComponent($locale, $component, $translation);

        return $component;
    }

    public static function makeTabs(callable $makeTab, Collection $locales) {
        return Forms\Components\Tabs::make()
            ->tabs($locales->map(function ($locale) use ($makeTab) {
                return $makeTab($locale);
            })
                ->toArray())
            ->columnSpanFull();
    }
}
