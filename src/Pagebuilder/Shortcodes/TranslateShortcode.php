<?php

namespace Feenstra\CMS\Pagebuilder\Shortcodes;

use Feenstra\CMS\Pagebuilder\Shortcodes\Shortcode;
use Illuminate\Support\Collection;
use Feenstra\CMS\I18n\Models\Translation;

class TranslateShortcode extends Shortcode {
    public static string $name = 'translate';

    public function resolve(Collection $arguments, array $data, ShortcodeProcessor $processor): mixed {
        $key = $arguments->keys()->first();

        // look in custom record translations
        if (isset($data['page']->record)) {
            $translation = Translation::get($key, $data['page']->record->unwrap(), 'custom');
            if ($translation) return $translation->translate();
        }

        // look in global translations
        $translation = Translation::get($key);
        if ($translation) return $translation->translate();

        return null;
    }
}
