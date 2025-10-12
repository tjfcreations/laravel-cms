<?php

namespace Feenstra\CMS\I18n\Filament\Actions;

use Feenstra\CMS\I18n\Filament\Support\TranslationsForm;
use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Tabs;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Models\Translation;
use Feenstra\CMS\I18n\Registry;
use Filament\Forms;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Filament\Forms\Set;
use Filament\Forms\Components\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TranslateAction extends Action {
    protected array $labels = [];

    protected array $richAttributes = [];

    protected function setUp(): void {
        parent::setUp();

        $this
            ->label('Vertalen')
            ->icon('heroicon-m-chat-bubble-left-right')
            ->color(Color::Emerald)
            ->form(function () {
                return [TranslationsForm::makeTabs([$this, 'makeTab'], Locale::allWithDefaultLast())];
            })
            ->fillForm(function (TranslatableInterface $record) {
                return $this->getData($record);
            })
            ->modalSubmitActionLabel('Opslaan')
            ->action(function (TranslatableInterface $record, array $data) {
                $this->handleSave($record, $data);
            })
            ->visible(fn() => Registry::isEnabled() && Locale::count() > 0);
    }

    public static function getDefaultName(): ?string {
        return 'translate';
    }

    /**
     * Specify the input labels.
     */
    public function labels(array $labels) {
        $this->labels = $labels;
        return $this;
    }

    /**
     * Specify which attributes should use a rich editor.
     */
    public function rich(array $richAttributes) {
        $this->richAttributes = $richAttributes;
        return $this;
    }

    /**
     * Check whether the given attribute is a rich attribute.
     */
    protected function isRichAttribute(string $attribute) {
        return in_array($attribute, $this->richAttributes);
    }

    /**
     * Get the form data for the given record.
     */
    protected function getData(TranslatableInterface $record): array {
        $data = [];

        foreach ($record->translations as $translation) {
            foreach ($translation->values() as $localeCode => $value) {
                if (!is_string($value) || empty($value)) continue;

                $group = $translation->group ?? 'ungrouped';

                // translation key might contains periods, which does not work with Arr::set()
                $data[$group] ??= [];
                $data[$group][$localeCode] ??= [];
                $data[$group][$localeCode][$translation->key] = $value;
            }
        }

        return $data;
    }

    public function makeTab(Locale $locale) {
        return Tabs\Tab::make($locale->name)
            ->schema(function (TranslatableInterface $record) use ($locale) {
                return [
                    ...$this->makeValueInputs($locale, $record),
                    $this->makeCustomKeyValueEditor($locale)
                ];
            });
    }

    protected function handleSave(TranslatableInterface $record, array $data) {
        $updatedTranslations = [];

        // ensure existing translations are deleted when keys are removed
        foreach ($record->translations as $translation) {
            $group = $translation->group ?? 'ungrouped';
            $key = $translation->key;

            foreach (Locale::all() as $locale) {
                // explicitly set null values for missing keys
                $data[$group][$locale->code][$key] ??= null;
            }
        }

        foreach ($data as $group => $data) {
            foreach ($data as $localeCode => $values) {
                foreach ($values as $key => $value) {
                    $translation = Translation::get($key, $record, $group);

                    if ($translation->set($localeCode, $value, Auth::user())) {
                        $translation->save();
                        $updatedTranslations[$translation->id] = $translation;
                    }
                }
            }
        }

        foreach ($updatedTranslations as $translation) {
            $translation->updateMachineTranslationsAsync();
        }
    }

    protected function makeCustomKeyValueEditor(Locale $locale): Forms\Components\KeyValue {
        return Forms\Components\KeyValue::make("custom.{$locale->code}")
            ->label("Lokale vertalingen ({$locale->name})")
            ->keyLabel('Sleutel')
            ->keyPlaceholder('bijv. zoeken, kopen')
            ->valueLabel('Vertaling')
            ->valuePlaceholder('bijv. suchen, search, kaufen, buy')
            ->addActionLabel('Vertaling toevoegen')
            ->hintColor(Color::Emerald)
            ->hintIcon(
                'heroicon-m-question-mark-circle',
                tooltip: str('Met lokale vertalingen kun je delen van de pagina-inhoud vertalen. Gebruik [translate vertaalsleutel] in de pagina-inhoud om een lokale vertaling te tonen.')
            );
    }

    protected function makeValueInputs(Locale $locale, TranslatableInterface $record): array {
        if ($locale->isDefault()) return [];

        $translatableAttributes = $record->getTranslatableAttributes();

        return collect($translatableAttributes)->map(function ($attribute) use ($locale, $record) {
            $translation = Translation::get($attribute, $record);
            $label = $this->labels[$attribute] ?? Str::ucfirst($attribute);

            $input = TranslationsForm::makeValueInput($locale, "ungrouped.{$locale->code}.{$attribute}", $this->isRichAttribute($attribute), $translation)
                ->label("{$label} ({$locale->name})");

            return $input;
        })->toArray();
    }
}
