<?php
    namespace Feenstra\CMS\Locale;

    use Feenstra\CMS\Pagebuilder\Registry as PagebuilderRegistry;
    use Illuminate\Support\Collection;
    use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;

    class Registry { 
        public static function translatables(): Collection {
            return PagebuilderRegistry::models()
                ->filter(fn($model) => $model instanceof TranslatableInterface);
        }
    }