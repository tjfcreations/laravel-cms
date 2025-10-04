<?php

namespace Feenstra\CMS\I18n\Traits;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Feenstra\CMS\I18n\Models\Translation;
use Feenstra\CMS\Pagebuilder\Traits\Selectable;
use Illuminate\Support\Collection;
use Feenstra\CMS\I18n\Models\Locale;

trait Translatable {
    use Selectable;

    public static function bootTranslatable() {
        // update translations on save
        static::saved(function (TranslatableInterface $record) {
            $defaultLocale = Locale::getDefault();

            // update translations for default locale to match record values
            foreach ($record->getTranslatableAttributes() as $attribute) {
                $translation = Translation::get($attribute, $record);
                $value = $record->{$attribute};

                if (is_string($value) && $value !== $translation->getValue($defaultLocale->code)) {
                    $translation->set($defaultLocale->code, $value, 'user');
                }
            }

            // update machine translations
            foreach ($record->translations as $translation) {
                $translation->updateMachineTranslations();
            }
        });
    }

    public function translate(string $attribute): mixed {
        if (in_array($attribute, $this->getTranslatableAttributes())) {
            $translation = Translation::get($attribute, $this);

            if ($translation->has()) {
                return $translation->translate();
            }
        }

        return self::__get($attribute);
    }

    public function getCustomTranslations(): Collection {
        return $this->translations()->where('group', 'custom')->get();
    }

    public function translations(): MorphMany {
        return $this->morphMany(Translation::class, 'record');
    }

    public function getTranslatableAttributes(): array {
        return is_array(@$this->translate) ? $this->translate : [];
    }
}
