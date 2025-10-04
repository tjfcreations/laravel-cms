<?php

namespace Feenstra\CMS\I18n\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

interface TranslatableInterface {
    public function translations(): MorphMany;

    public function translate(string $attribute): mixed;

    public function getCustomTranslations(): Collection;

    public function getTranslatableAttributes(): array;
}
