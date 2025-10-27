<?php

namespace Feenstra\CMS\Pagebuilder\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Feenstra\CMS\I18n\Models\Translation;

class UpdateAllMachineTranslations implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void {
        Log::debug('Updating missing or outdated machine translations...');

        $translations = Translation::all();
        foreach ($translations as $translation) {
            $translation->updateMachineTranslations();
        }

        Log::info('Successfully updated machine translations.');
    }
}
