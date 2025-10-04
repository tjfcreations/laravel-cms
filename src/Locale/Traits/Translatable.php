<?php
    namespace Feenstra\CMS\Locale\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Feenstra\CMS\Locale\Models\Translation;
use Feenstra\CMS\Pagebuilder\Traits\Selectable;

    trait Translatable {
        use Selectable;

        public function translate(string $attribute) {
            if(in_array($attribute, $this->getTranslatableAttributes())) {
                $translation = Translation::get($attribute, $this);
                
                if($translation->has()) {
                    return $translation->translate();
                }
            }

            return self::__get($attribute);
        }

        public function translations(): MorphMany {
            return $this->morphMany(Translation::class, 'record');
        }

        public function getTranslatableAttributes(): array {
            return is_array(@$this->translate) ? $this->translate : [];
        }
    }