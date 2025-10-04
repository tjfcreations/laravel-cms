<?php

namespace Feenstra\CMS\Pagebuilder;

use Feenstra\CMS\Pagebuilder\Support\Block;
use Feenstra\CMS\Pagebuilder\Shortcodes\Shortcode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Throwable;
use Composer\Autoload\ClassLoader;

class Registry {
    public static function blocks(): Collection {
        return self::getInstances(self::cache(
            'pagebuilder.blocks',
            fn() =>
            self::discoverClasses(Block::class)
        ));
    }

    public static function shortcodes(): Collection {
        return self::getInstances(self::cache(
            'pagebuilder.shortcodes',
            fn() =>
            self::discoverClasses(Shortcode::class)
        ));
    }

    public static function models(): Collection {
        return self::getInstances(self::cache(
            'app.models',
            fn() =>
            self::discoverClasses(Model::class)
        ));
    }

    protected static function getInstances(array $models): Collection {
        return collect($models)
            ->map(fn($model) => (new $model()));
    }

    protected static function cache(string $key, callable $callback): array {
        // no caching, just run the callback
        if (app()->isLocal() || app()->runningInConsole()) {
            return $callback();
        }

        try {
            return cache()->rememberForever($key, $callback);
        } catch (\Exception) {
            return $callback();
        }
    }

    /**
     * Discover classes that optionally extend $parentClass.
     */
    protected static function discoverClasses(?string $parentClass = null): array {
        $classLoader = require base_path('vendor/autoload.php');
        $classes = array_keys($classLoader->getClassMap());

        if (blank($parentClass)) {
            return $classes;
        }

        return array_filter($classes, function ($class) use ($parentClass) {
            $vendor = strtok($class, '\\');
            if ($vendor !== 'App' && $vendor !== 'Feenstra') return false;

            if (!class_exists($class)) return false;

            return is_subclass_of($class, $parentClass);
        });
    }
}
