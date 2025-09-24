<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Filament\Actions;

    use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;
    use Filament\Actions\Action;
    use Filament\Support\Colors\Color;
    use Filament\Forms\Components\Tabs;
    use FeenstraDigital\LaravelCMS\Locale\Models\Locale;
    use FeenstraDigital\LaravelCMS\Locale\Models\Translation;
    use Filament\Forms;
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
                    return Locale::allNotDefault()->isEmpty();
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
                Arr::set($data, "{$translation->key}.{$translation->locale}", $translation->value);
            }

            return $data;
        }

        protected function handleSave(TranslatableInterface $record, array $data) {
            foreach($record->getTranslatableAttributes() as $attribute) {
                if(!is_array(@$data[$attribute])) continue;

                $translations = $data[$attribute];
                foreach($translations as $locale => $translation) {
                    if(isset($translation) && !empty($translation)) {
                        Translation::setForLocale($locale, $attribute, $translation, $record);
                    } else {
                        Translation::removeForLocale($locale, $attribute, $record);
                    }
                }
            }
        }

        protected function getTabs() {
            return Tabs::make()
                ->tabs(function() {
                    return Locale::allNotDefault()->map(function($locale) {
                        return $this->getTab($locale);
                    })->toArray();
                });
        }

        protected function getTab(Locale $locale) {
            return Tabs\Tab::make($locale->name)
                ->schema(function(TranslatableInterface $record) use($locale) {
                    $translatableAttributes = $record->getTranslatableAttributes();

                    return collect($translatableAttributes)->map(function($attribute) use($locale) {
                        $label = $this->labels[$attribute] ?? Str::ucfirst($attribute);

                        return $this->getComponent($attribute, $locale)
                            ->label("{$label} ({$locale->name})");
                    })->toArray();
                });
        }

        protected function getComponent(string $attribute, Locale $locale) {
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