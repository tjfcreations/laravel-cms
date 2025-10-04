<?php

namespace Feenstra\CMS\I18n\Models;

use Illuminate\Database\Eloquent\Model;
use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Feenstra\CMS\I18n\Support\PatternEscaper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;

class Translation extends Model {
    protected $table = 'fd_cms_translations';

    protected $casts = [
        'translations' => 'array'
    ];

    public $timestamps = false;

    protected $guarded = [];

    public function record(): MorphTo {
        return $this->morphTo();
    }

    /**
     * Translate to the given locale (current app locale by default). 
     * Will fall back to the default locale if no translation exists for the given locale.
     */
    public function translate(?string $localeCode = null): string {
        $localeCode = $localeCode ?? app()->currentLocale();
        $value = $this->getValue($localeCode);

        if (empty($value)) {
            $defaultLocale = Locale::getDefault();
            $value = $this->getValue($defaultLocale->code);
        }

        if (empty($value)) {
            return $this->key;
        }

        return $value;
    }

    /**
     * Get all translation values.
     */
    public function values(): array {
        return collect($this->translations)->map(fn($t) => @$t['value'])->toArray();
    }

    /**
     * Get the translation value for a given locale.
     */
    protected function getValue(string $localeCode): ?string {
        return Arr::get($this->translations, "{$localeCode}.value");
    }

    /**
     * Check if a translation exists for the given locale (current locale by default).
     */
    public function has(?string $locale = null): bool {
        $locale = $locale ?? app()->currentLocale();
        return !empty(Arr::get($this->translations, "$locale.value"));
    }

    /**
     * Update the translation for a given locale.
     */
    public function set(string $locale, mixed $value = null, ?string $source = null, bool $save = true): self {
        $value = is_string($value) ? $value : null;

        $translations = (array) $this->translations;
        Arr::set($translations, "$locale.value", $value);
        $this->translations = $translations;

        if (is_string($source)) {
            $this->setMetaProperty($locale, 'source', $source, false);
        }

        $this->setMetaProperty($locale, 'updated_at', Carbon::now()->toIso8601String());

        if ($save) $this->save();

        return $this;
    }

    public function clear(bool $save = true): self {
        $this->translations = [];

        if ($save) $this->save();

        return $this;
    }

    /**
     * Remove the translation for a given locale.
     */
    public function remove(string $locale, bool $save = true): self {
        return $this->set($locale, null, null, $save);
    }

    public function getMeta(string $locale): array {
        return (array) Arr::get($this->translations, "$locale.meta");
    }

    public function setMeta(string $locale, array $meta, bool $save = true): self {
        if (empty($meta)) return $this;

        $mergedMeta = array_replace($this->getMeta($locale), $meta);
        $translations = (array) $this->translations;
        Arr::set($translations, "$locale.meta", $mergedMeta);
        $this->translations = $translations;

        if ($save) $this->save();

        return $this;
    }

    public function getMetaProperty(string $locale, string $key): mixed {
        return Arr::get($this->getMeta($locale), $key);
    }

    public function setMetaProperty(string $locale, string $key, mixed $value, bool $save = true): self {
        return $this->setMeta($locale, [$key => $value], $save);
    }

    public function isMachineTranslation(string $locale): bool {
        return $this->has($locale) && $this->getMetaProperty($locale, 'source') === 'machine';
    }

    public function isUserTranslation(string $locale): bool {
        return $this->has($locale) && $this->getMetaProperty($locale, 'source') === 'user';
    }

    /**
     * Check if any machine translations are outdated compared to the default locale.
     */
    public function hasOutdatedMachineTranslations() {
        $defaultLocale = Locale::getDefault();
        $defaultUpdatedAt = $this->getMetaProperty($defaultLocale->code, 'updated_at');
        if (!$defaultUpdatedAt) return false;

        $outdatedLocales = Locale::allMachineTranslatable()
            ->filter(fn($l) => $this->isMachineTranslation($l->code))
            ->filter(fn($l) => $this->getMetaProperty($l->code, 'updated_at') < $defaultUpdatedAt);

        return $outdatedLocales->isNotEmpty();
    }

    /**
     * Regenerate missing (empty) machine translations.
     */
    public function updateMissingMachineTranslations(): self {
        $targetLocales = Locale::allMachineTranslatable()
            ->filter(fn($l) => !$this->has($l->code));
        
        $translations = $this->generateMachineTranslations($targetLocales);
        foreach($translations as $localeCode => $value) {
            $this->set($localeCode, $value, 'machine', false);
        }

        $this->save();

        return $this;
    }

    /**
     * Regenerate all machine translations (except user-edited ones).
     */
    public function updateAllMachineTranslations(): self {
        $targetLocales = Locale::allMachineTranslatable()
            ->filter(fn($l) => !$this->isUserTranslation($l->code));

        $translations = $this->generateMachineTranslations($targetLocales);
        foreach($translations as $localeCode => $value) {
            $this->set($localeCode, $value, 'machine', false);
        }

        $this->save();

        return $this;
    }
    
    /**
     * Generates machine translations using the Google Cloud Translation API.
     */
    protected function generateMachineTranslations(Collection $targetLocales): array {
        $sourceLocale = Locale::getDefault();

        // ensure source locale is not in target locales
        $targetLocales = $targetLocales->filter(fn($l) => !$l->is($sourceLocale));
        if(empty($targetLocales)) return [];

        $sourceValue = $this->getValue($sourceLocale->code);
        if (empty($sourceValue)) return [];

        $client = new TranslationServiceClient([
            'credentials' => config('fd-cms.google_application_credentials')
        ]);
        $formattedParent = $client->locationName(config('fd-cms.google_cloud_project'), 'global');

        Log::debug("Generating machine translations in " . $targetLocales->pluck('code')->implode(', ') . " for [Translation {$this->id}] ({$sourceValue})");

        // replace shortcodes with placeholders to prevent them from being translated
        $escaper = new PatternEscaper($sourceValue);
        $escapedSourceValue = $escaper->escape('/\[[^\]]+\]/');

        $translations = [];
        foreach ($targetLocales as $targetLocale) {
            $request = (new TranslateTextRequest())
                ->setContents([$escapedSourceValue])
                ->setSourceLanguageCode($sourceLocale->code)
                ->setTargetLanguageCode($targetLocale->code)
                ->setParent($formattedParent);

            $response = $client->translateText($request);
            $escapedTargetValue = @$response->getTranslations()[0]->getTranslatedText();
            
            if(is_string($escapedTargetValue)) {
                // replace placeholders with shortcodes again
                $targetValue = $escaper->unescape($escapedTargetValue);
                $translations[$targetLocale->code] = $targetValue;
            }
        }

        return $translations;
    }

    /**
     * Get or create a translation.
     */
    public static function get(string $key, ?TranslatableInterface $record = null, string $group = ''): Translation {
        $translation = self::getTranslations($record)->where([
            'key' => $key,
            'group' => empty($group) ? null : $group
        ])->first();

        // dd($key, $translation);

        if (!$translation) {
            $translation = new Translation();
            $translation->key = $key;
            $translation->group = $group;
            $translation->translations = [];
            $translation->record()->associate($record);
        }

        return $translation;
    }

    protected static function getTranslations(?TranslatableInterface $record = null): MorphMany|Builder {
        return isset($record) ? $record->translations() : Translation::query();
    }

    public function updateAllMachineTranslationsAsync(): self {
        dispatch(fn() => $this->updateAllMachineTranslations()->save())
            ->afterResponse();

        return $this;
    }
    
    public function updateMissingMachineTranslationsAsync(): self {
        dispatch(fn() => $this->updateMissingMachineTranslations()->save())
            ->afterResponse();

        return $this;
    }
}
