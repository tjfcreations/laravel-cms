<?php

namespace Feenstra\CMS\Pagebuilder\Commands;

use Illuminate\Console\Command;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Support\Str;

class MakePagebuilderBlock extends Command
{
    use CanManipulateFiles;

    protected $signature = 'make:pagebuilder-block {name}';
    protected $description = 'Create a new Pagebuilder block class and view';

    public function handle()
    {
        $slug = Str::slug($this->argument('name'));
        $class = Str::pascal($slug).'Block';
        $label = Str::ucfirst(Str::lower(Str::snake($slug, ' ')));

        $classPath = "app/Pagebuilder/Blocks/{$class}.php";
        $namespace = "App\\Pagebuilder\\Blocks";
        $view = "pagebuilder.blocks.{$slug}";

        // Copy stub to app using Filament's method
        $this->copyStubToApp(
            'PagebuilderBlock',
            $classPath,
            [
                'namespace' => $namespace,
                'label' => $label,
                'class' => $class,
                'view' => $view,
            ]
        );

        // Create blade view using artisan callSilent with snake_case name
        $this->callSilent('make:view', ['name' => $view]);

        $this->info("Block class created: " . base_path($classPath));
        $this->info("Block view created: " . resource_path("views/pagebuilder/blocks/{$slug}.blade.php"));
    }
}
