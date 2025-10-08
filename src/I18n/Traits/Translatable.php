<?php

namespace Feenstra\CMS\I18n\Traits;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Feenstra\CMS\I18n\Models\Translation;
use Illuminate\Support\Collection;
use Feenstra\CMS\I18n\Models\Locale;
use Feenstra\CMS\I18n\Registry;

trait Translatable {
    public static function bootTranslatable() {
        if (Registry::isDisabled()) return false;

        // update translations on save
        static::saved(function (TranslatableInterface $record) {
            $defaultLocale = Locale::getDefault();

            // update translations for default locale to match record values
            foreach ($record->getTranslatableAttributes() as $attribute) {
                $translation = Translation::get($attribute, $record);
                $value = @$record->{$attribute};

                if ($translation->set($defaultLocale->code, $value, 'user')) {
                    $translation->save();
                }
            }

            // update machine translations
            foreach ($record->translations as $translation) {
                $translation->updateMachineTranslationsAsync();
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

    /**
     * Determine if machine translations should be generated for the given attribute.
     */
    public function shouldGenerateMachineTranslation(string $attribute): bool {
        return true;
    }

    /**
     * Get the translatable attributes for the model.
     */
    public function getTranslatableAttributes(): array {
        return is_array(@$this->translate) ? $this->translate : [];
    }
}
