<?php

namespace Feenstra\CMS\I18n;

use Feenstra\CMS\Pagebuilder\Registry as PagebuilderRegistry;
use Illuminate\Support\Collection;
use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\I18n\Models\Locale;
use Illuminate\Support\Facades\File;

class Registry {
    public static function translatables(): Collection {
        return once(fn() => PagebuilderRegistry::models()
            ->filter(fn($model) => $model instanceof TranslatableInterface));
    }

    public static function isMachineTranslationEnabled() {
        return !!config('fd-cms.i18n.enabled', true) && Locale::where('is_machine_translatable', true)->count() > 0;
    }

    public static function hasGoogleCloudCredentials(): bool {
        $filepath = config('fd-cms.google_application_credentials', null);
        return $filepath && File::exists($filepath);
    }

    public static function isEnabled() {
        return !!config('fd-cms.i18n.enabled', true);
    }

    public static function isDisabled() {
        return !self::isEnabled();
    }
}
