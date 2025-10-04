<?php
namespace Feenstra\CMS\Locale\Jobs;

use Feenstra\CMS\Locale\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Feenstra\CMS\Locale\Models\Locale;

class GenerateMachineTranslations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Translation $translation,
    ) {}

    public function handle(): void
    {
        foreach (Locale::all() as $locale) {
            $this->translation->generateMachineTranslation($locale);
        }
    }
}