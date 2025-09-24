<?php

namespace FeenstraDigital\LaravelCMS\Pagebuilder\Commands;

use Illuminate\Console\Command;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Support\Str;

class MakePagebuilderShortcode extends Command
{
    use CanManipulateFiles;

    protected $signature = 'make:pagebuilder-shortcode {name}';
    protected $description = 'Create a new pagebuilder shortcode class';

    public function handle()
    {
        $name = Str::slug($this->argument('name'));
        $class = Str::pascal($name).'Shortcode';

        $classPath = "app/Pagebuilder/Shortcodes/{$class}.php";
        $namespace = "App\\Pagebuilder\\Shortcodes";

        // Copy stub to app using Filament's method
        $this->copyStubToApp(
            'PagebuilderShortcode',
            $classPath,
            [
                'namespace' => $namespace,
                'name' => $name,
                'class' => $class
            ]
        );

        $this->info("Shortcode class created: " . base_path($classPath));
    }
}
