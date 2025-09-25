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

        protected function handleSave(TranslatableInterface $record, array $data) {
            $attributeData = collect($data)->except('_custom')->toArray();
            $customData = $data['_custom'];

            // save attribute translations
            foreach($attributeData as $key => $translations) {
                $translation = Translation::get($key, $record);

                foreach($translations as $locale => $value) {
                    // ignore if translation was not changed
                    if($translation->translate($locale) === $value) continue;
                    $translation->set($locale, $value, 'user');
                }
            }

            // delete all custom translations
            $record->translations()->where('group', 'custom')->delete();

            // save custom translations
            foreach($customData as $locale => $items) {
                foreach($items as $key => $value) {
                    $key = Str::slug($key, '_');
                    $translation = Translation::get($key, $record, 'custom');

                    // ignore if translation was not changed
                    if($translation->translate($locale) === $value) continue;
                    $translation->set($locale, $value, 'user');
                }
            }
        }

        protected function getTabs() {
            return Tabs::make()
                ->tabs(function() {
                    return Locale::all()->map(function($locale) {
                        return $this->getTab($locale);
                    })->toArray();
                });
        }

        protected function getTab(Locale $locale) {
            return Tabs\Tab::make($locale->name)
                ->schema(function(TranslatableInterface $record) use($locale) {
                    return [
                        ...$this->makeAttributeInputs($record, $locale),
                        $this->makeCustomTranslationsEditor($record, $locale)
                    ];
                });
        }

        protected function makeAttributeInputs(TranslatableInterface $record, Locale $locale): array {
            $translatableAttributes = $record->getTranslatableAttributes();

            return collect($translatableAttributes)->map(function($attribute) use($locale) {
                $label = $this->labels[$attribute] ?? Str::ucfirst($attribute);

                return $this->makeAttributeInput($attribute, $locale)
                    ->label("{$label} ({$locale->name})");
            })->toArray();
        }

        protected function makeCustomTranslationsEditor(TranslatableInterface $record, Locale $locale): KeyValue {
            return KeyValue::make("_custom.{$locale->code}")
                ->label("Lokale vertalingen ({$locale->name})")
                ->keyLabel('Vertaalsleutel')
                ->valueLabel('Vertaling')
                ->hintIcon(
                    'heroicon-m-question-mark-circle', 
                    tooltip: str('Met lokale vertalingen kun je makkelijk je pagina-inhoud vertalen. Gebruik [translate vertaalsleutel] in je pagina-inhoud om een lokale vertaling te tonen.'));
        }

        protected function makeAttributeInput(string $attribute, Locale $locale) {
            $name = "{$attribute}.{$locale->code}";

            if($this->isRichAttribute($attribute)) {
                return Forms\Components\RichEditor::make($name);
            }

            return Forms\Components\TextInput::make($name)
                ->suffixAction(
                    Forms\Components\Actions\Action::make('reset')
                        ->icon('heroicon-m-trash')
                        ->color(Color::Red)
                        ->action(function (Set $set, $state) use($name) {
                            $set($name, null);
                        })
                );
        }
    }