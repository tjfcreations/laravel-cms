<?php

namespace Feenstra\CMS\I18n;

use Feenstra\CMS\Pagebuilder\Registry as PagebuilderRegistry;
use Illuminate\Support\Collection;
use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;

class Registry {
    public static function translatables(): Collection {
        return PagebuilderRegistry::models()
            ->filter(fn($model) => $model instanceof TranslatableInterface);
    }

    public static function isEnabled() {
        return !!config('feenstra.cms.i18n.enabled', true);
    }

    public static function isDisabled() {
        return !self::isEnabled();
    }
}
