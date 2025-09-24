<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder;

use FeenstraDigital\LaravelCMS\Pagebuilder\Support\Block;
use FeenstraDigital\LaravelCMS\Pagebuilder\Support\Shortcode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Throwable;
use FeenstraDigital\LaravelCMS\Pagebuilder\Concerns\Translatable;

class Registry
{
    public static function blocks(): Collection
    {
        return self::getInstances(self::cache('pagebuilder.blocks', fn () =>
            self::discoverAppClasses(Block::class)
        ));
    }

    public static function shortcodes(): Collection
    {
        return self::getInstances(self::cache('pagebuilder.shortcodes', fn () =>
            self::discoverAppClasses(Shortcode::class)
        ));
    }

    public static function models(): Collection
    {
        return self::getInstances(self::cache('app.models', fn () =>
            self::discoverAppClasses(Model::class)
        ));
    }

    protected static function getInstances(array $models): Collection {
        return collect($models)
            ->map(fn($model) => (new $model()));
    }

    protected static function cache(string $key, callable $callback): array
    {
        if (app()->isLocal()) {
            // in local: no caching, just run the callback
            return $callback();
        }

        // in production: cache forever
        return cache()->rememberForever($key, $callback);
    }

    /**
     * Discover all classes that optionally extend $parentClass.
     */
    protected static function discoverAppClasses(?string $parentClass = null): array
    {
        $classes = self::getAppClasses();

        if (blank($parentClass)) {
            return $classes->all();
        }

        // Filter by parent class / interface
        return $classes->filter(function ($class) use ($parentClass) {
            try {
                if (! is_subclass_of($class, $parentClass)) {
                    return false;
                }

                $ref = new ReflectionClass($class);
                return ! $ref->isAbstract();
            } catch (Throwable) {
                return false;
            }
        })->values()->all();
    }

    /**
     * Get all app classes (using Composer classmap in production, filesystem in local).
     */
    protected static function getAppClasses()
    {
        $namespace = 'App\\';

        if (app()->isLocal()) {
            // scan filesystem, map to FQCNs
            return collect(File::allFiles(app_path()))
                ->map(function ($file) use ($namespace) {
                    $relative = Str::after($file->getPathname(), app_path() . DIRECTORY_SEPARATOR);
                    $class = $namespace . str_replace(
                        [DIRECTORY_SEPARATOR, '.php'],
                        ['\\', ''],
                        $relative
                    );
                    return $class;
                })
                ->filter(fn(string $class) => class_exists($class))
                ->values();
        }

        // production: use composer optimized classmap
        $classLoader = require base_path('vendor/autoload.php');

        return collect($classLoader->getClassMap())
            ->keys()
            ->filter(fn(string $class) => Str::startsWith($class, $namespace))
            ->values();
    }
}
