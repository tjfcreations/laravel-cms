<?php
    namespace Feenstra\CMS\I18n\Interfaces;

    use Illuminate\Database\Eloquent\Relations\MorphMany;

    interface TranslatableInterface {
        public function translations(): MorphMany;
        public function getTranslatableAttributes(): array;
    }