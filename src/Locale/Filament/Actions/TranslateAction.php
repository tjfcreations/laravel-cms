<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Filament\Actions;

    use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;
    use Filament\Actions\Action;
    use Filament\Support\Colors\Color;
    use Filament\Forms\Components\Tabs;
    use FeenstraDigital\LaravelCMS\Locale\Models\Locale;
    use FeenstraDigital\LaravelCMS\Locale\Models\Translation;
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
                    return [
                        $this->getTabs()
                    ];
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

        protected function getTabs() {
            return Tabs::make()
                ->tabs(function() {
                    return Locale::allWithDefaultLast()->map(function($locale) {
                        return $this->getTab($locale);
                    })->toArray();
                });
        }

        protected function getTab(Locale $locale) {
            return Tabs\Tab::make($locale->name)
                ->schema(function(TranslatableInterface $record) use($locale) {
                    return [
                        ...$this->makeInputs($record, $locale),
                        $this->makeCustomTranslationsEditor($record, $locale)
                    ];
                });
        }

        protected function makeInputs(TranslatableInterface $record, Locale $locale): array {
            $translatableAttributes = $record->getTranslatableAttributes();

            return collect($translatableAttributes)->map(function($attribute) use($locale, $record) {
                $translation = Translation::get($attribute, $record);
                $label = $this->labels[$attribute] ?? Str::ucfirst($attribute);

                $input = $this->makeInput("{$attribute}.{$locale->code}", $translation, $locale, $this->isRichAttribute($attribute))
                    ->label("{$label} ({$locale->name})");

                // hide input for default locale if empty
                if($locale->isDefault()) {
                    $input->hidden(fn($get) => empty($get("{$attribute}.{$locale->code}")));
                }
                                
                return $input;
            })->toArray();
        }

        public static function makeInput(string $name, Translation $translation, Locale $locale, bool $isRich = false) {
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

            self::updateInputHint($input, $translation, $locale);

            return $input;
        }

        protected function makeCustomTranslationsEditor(TranslatableInterface $record, Locale $locale): KeyValue {
            return KeyValue::make("_custom.{$locale->code}")
                ->label("Lokale vertalingen ({$locale->name})")
                ->keyLabel('Vertaalsleutel')
                ->valueLabel('Vertaling')
                ->hintColor('primary')
                ->hintIcon(
                    'heroicon-m-question-mark-circle', 
                    tooltip: str('Met lokale vertalingen kun je makkelijk je pagina-inhoud vertalen. Gebruik [translate vertaalsleutel] in je pagina-inhoud om een lokale vertaling te tonen.'));
        }

        public static function updateInputHint(Component $component, Translation $translation, Locale $locale) {
            if($translation->isMachineTranslation($locale->code)) {
                $component
                    ->hint('Automatisch gegenereerd')
                    ->hintIcon('heroicon-s-bug-ant', tooltip: 'Deze vertaling is automatisch gegenereerd.')
                    ->hintColor(Color::Blue);
            }

            return $component;
        }
    }