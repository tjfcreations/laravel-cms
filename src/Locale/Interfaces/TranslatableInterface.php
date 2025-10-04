<?php
    namespace Feenstra\CMS\Locale\Interfaces;

    use Illuminate\Database\Eloquent\Relations\MorphMany;

    interface TranslatableInterface {
        public function translations(): MorphMany;
        public function getTranslatableAttributes(): array;
    }