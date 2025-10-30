<?php

namespace Feenstra\CMS\Pagebuilder\Shortcodes;

use Feenstra\CMS\Pagebuilder\Shortcodes\Shortcode;
use Illuminate\Support\Collection;
use Feenstra\CMS\I18n\Models\Translation;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Feenstra\CMS\I18n\Models\Locale;

class TranslateShortcode extends Shortcode {
    public static string $name = 'translate';

    public function resolve(Collection $arguments, array $data, ShortcodeProcessor $processor): mixed {
        $key = $arguments->keys()->first();
        if ($key === 'key') {
            $key = $arguments->get('key');
        }

        $translationSources = collect();

        if (is_array(@$data['translationSources'])) {
            $translationSources->push(...$data['translationSources']);
        }

        if (isset($data['page']->record)) {
            $translationSources->push($data['page']->record->unwrap());
        }

        if (isset($data['page']->page)) {
            $translationSources->push($data['page']->page->unwrap());
        }

        foreach ($translationSources as $record) {
            $translation = Translation::get($key, $record, 'custom');
            if ($translation->exists()) return $translation->translate();

            $translation = Translation::get($key, $record);
            if ($translation->exists()) return $translation->translate();
        }

        // look in global translations
        $translation = Translation::get($key);
        if ($translation->exists()) return $translation->translate();

        return null;
    }
}
