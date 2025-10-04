<?php

namespace Feenstra\CMS\Pagebuilder\Shortcodes;

use Illuminate\Support\Collection;

abstract class Shortcode {
    public static string $name;

    public abstract function resolve(Collection $arguments, array $data, ShortcodeProcessor $processor): mixed;
}
