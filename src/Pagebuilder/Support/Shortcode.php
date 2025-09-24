<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Support;

    use Illuminate\Support\Collection;

    abstract class Shortcode {
        public static string $name;

        public abstract function resolve(Collection $arguments, array $data): mixed;
    }