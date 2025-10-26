<?php

namespace Feenstra\CMS\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionServiceProvider;

class SetupCommand extends Command {
    protected $signature = 'cms:setup';
    protected $description = 'Setup common package';

    public function handle(): int {
        $this->info('Publishing migrations and config files...');

        // publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'fd-cms-migrations',
            '--force' => true,
        ]);

        $this->newLine(4);
        $this->info('Setting up Shield...');

        $this->call('shield:setup');

        $this->newLine(4);
        $this->info('Setup finished.');

        return Command::SUCCESS;
    }
}
