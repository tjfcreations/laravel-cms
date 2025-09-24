<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use FeenstraDigital\LaravelCMS\Locale\Models\Translation;
use FeenstraDigital\LaravelCMS\Pagebuilder\Traits\Selectable;

    trait Translatable {
        use Selectable;

        public function translations(): MorphMany {
            return $this->morphMany(Translation::class, 'model');
        }

        public function getTranslatableAttributes() {
            return is_array(@$this->translate) ? $this->translate : [];
        }

        public function getAttribute($key) {
            if (!in_array($key, $this->translate)) {
                return parent::getAttribute($key);
            }

            // get the translated attribute value, or the default attribute value
            return Translation::get($key, $this) ?? parent::getAttribute($key);
        }
    }