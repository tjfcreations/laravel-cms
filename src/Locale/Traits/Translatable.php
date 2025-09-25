<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use FeenstraDigital\LaravelCMS\Locale\Models\Translation;
use FeenstraDigital\LaravelCMS\Pagebuilder\Traits\Selectable;

    trait Translatable {
        use Selectable;

        public function translations(): MorphMany {
            return $this->morphMany(Translation::class, 'record');
        }

        public function getTranslatableAttributes(): array {
            return is_array(@$this->translate) ? $this->translate : [];
        }
    }