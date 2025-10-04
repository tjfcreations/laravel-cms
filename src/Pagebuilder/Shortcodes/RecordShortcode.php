<?php

namespace Feenstra\CMS\Pagebuilder\Shortcodes;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\Pagebuilder\Shortcodes\Shortcode;
use Illuminate\Support\Collection;

class RecordShortcode extends Shortcode {
    public static string $name = 'record';

    public function resolve(Collection $arguments, array $data, ShortcodeProcessor $processor): mixed {
        $attribute = $arguments->keys()->first();

        $record = @$data['page']->record->unwrap();
        if ($record instanceof TranslatableInterface) {
            $result = $record->translate($attribute);
        } else {
            $result = $record->$attribute;
        }

        return $processor->process($result);
    }
}
