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
    public static function makeValueInput(Locale $locale, string $name, bool $isRich = false, ?Translation $translation = null) {
        if ($isRich) {
            $input = Forms\Components\RichEditor::make($name);
        } else {
            $input = Forms\Components\TextInput::make($name)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('reset')
                        ->icon('heroicon-m-trash')
                        ->color(Color::Red)
                        ->action(function (Set $set, $state) use ($name) {
                            $set($name, null);
                        })
                );
        }

        // show whether the value is machine generated
        if ($translation) {
            $formattedDate = $translation->updatedAt($locale->code)->format('d-m-Y \o\m H:i.');

            if ($translation->isMachineTranslation($locale->code)) {
                $input
                    ->hintIcon('heroicon-s-bolt', tooltip: 'Automatisch vertaald op ' . $formattedDate)
                    ->hintColor(Color::Purple);
            } elseif ($translation->isUserTranslation($locale->code)) {
                $input
                    ->hintIcon('heroicon-s-clock', tooltip: 'Handmatig aangepast op ' . $formattedDate);
            }
        }

        return $input;
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
