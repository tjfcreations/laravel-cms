<?php
namespace FeenstraDigital\LaravelCMS\Pagebuilder\Shortcodes;

use FeenstraDigital\LaravelCMS\Pagebuilder\Shortcodes\Shortcode;
use Illuminate\Support\Collection;

class RecordShortcode extends Shortcode {
    public static string $name = 'record';

    public function resolve(Collection $arguments, array $data): mixed {
        $key = $arguments->keys()->first();
        return @$data['page']->record->$key;
    }
}
