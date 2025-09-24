<?php
    namespace FeenstraDigital\LaravelCMS\Locale;

    use FeenstraDigital\LaravelCMS\Pagebuilder\Registry as PagebuilderRegistry;
    use Illuminate\Support\Collection;
    use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;

    class Registry { 
        public static function translatables(): Collection {
            return PagebuilderRegistry::models()
                ->filter(fn($model) => $model instanceof TranslatableInterface);
        }
    }