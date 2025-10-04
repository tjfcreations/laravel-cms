<?php
    namespace Feenstra\CMS\I18n\Filament\Actions;

    use Feenstra\CMS\I18n\Filament\Support\TranslationsForm;
    use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
    use Filament\Actions\Action;
    use Filament\Support\Colors\Color;
    use Filament\Forms\Components\Tabs;
    use Feenstra\CMS\I18n\Models\Locale;
    use Feenstra\CMS\I18n\Models\Translation;
    use Filament\Forms;
    use Filament\Forms\Components\KeyValue;
    use Filament\Forms\Form;
    use Filament\Resources\Pages\EditRecord;
    use Illuminate\Support\Str;
    use Illuminate\Support\Arr;
    use Filament\Forms\Set;
    use Filament\Forms\Components\Component;
    use Illuminate\Support\Collection;

    class TranslateAction extends Action {
        protected array $labels = [];

        protected array $richAttributes = [];

        protected function setUp(): void {
            parent::setUp();

            $this
                ->label('Vertalen')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->color(Color::Emerald)
                ->form(function() {
                    return [ TranslationsForm::getTabs() ];
                })
                ->fillForm(function(TranslatableInterface $record) {
                    return $this->getData($record);
                })
                ->modalSubmitActionLabel('Opslaan')
                ->action(function (TranslatableInterface $record, array $data) {
                    $this->handleSave($record, $data);
                })
                ->hidden(function() {
                    return Locale::all()->isEmpty();
                });
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
         * Whether the given attribute is a rich attribute.
         */
        protected function isRichAttribute(string $attribute) {
            return in_array($attribute, $this->richAttributes);
        }

        protected function getData(TranslatableInterface $record): array {
            $data = [];

            foreach($record->translations as $translation) {
                foreach($translation->values() as $locale => $value) {
                    if($translation->group === 'custom') {
                        Arr::set($data, "_custom.{$locale}.{$translation->key}", $value);
                    } else {
                        Arr::set($data, "{$translation->key}.{$locale}", $value);
                    }
                }
            }

            return $data;
        }

        protected function handleSave(TranslatableInterface $record, array $rawData) {
            $dataByGroup = [
                '' => collect($rawData)->except('_custom')->toArray(),
                'custom' => $rawData['_custom']
            ];

            dd($dataByGroup);

            // save translations
            foreach($dataByGroup as $group => $data) {
                foreach($data as $key => $values) {
                    $translation = Translation::get($key, $record, $group);

                    $translation->clear(false);

                    foreach($values as $locale => $value) {
                        // ignore if translation was not changed
                        if($translation->translate($locale) === $value) continue;
                        $translation->set($locale, $value, 'user', false);
                    }

                    $translation->save();

                    $translation->updateMachineTranslations();
                }
            }
        }

        protected function makeValueInputs(Locale $locale, TranslatableInterface $record): array {
            $translatableAttributes = $record->getTranslatableAttributes();

            return collect($translatableAttributes)->map(function($attribute) use($locale, $record) {
                $translation = Translation::get($attribute, $record);
                $label = $this->labels[$attribute] ?? Str::ucfirst($attribute);

                $input = TranslationsForm::makeValueInput($locale, "{$attribute}.{$locale->code}", $this->isRichAttribute($attribute), $translation)
                    ->label("{$label} ({$locale->name})");

                // hide input for default locale if empty
                if($locale->isDefault()) {
                    $input->hidden(fn($get) => empty($get("{$attribute}.{$locale->code}")));
                }
                                
                return $input;
            })->toArray();
        }
    }