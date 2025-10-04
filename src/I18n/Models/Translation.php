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
use Carbon\CarbonInterface;
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
    public function getValue(string $localeCode): ?string {
        return Arr::get($this->translations, "{$localeCode}.value");
    }

    /**
     * Check if a translation exists for the given locale (current locale by default).
     */
    public function has(?string $localeCode = null): bool {
        $localeCode = $localeCode ?? app()->currentLocale();
        return !empty(Arr::get($this->translations, "$localeCode.value"));
    }

    /**
     * Update the translation for a given locale.
     */
    public function set(string $localeCode, mixed $value = null, ?string $source = null, bool $save = true): self {
        $value = is_string($value) ? $value : null;

        $translations = (array) $this->translations;
        Arr::set($translations, "$localeCode.value", $value);
        $this->translations = $translations;

        if (is_string($source)) {
            $this->setMetaProperty($localeCode, 'source', $source, false);
        }

        $this->setMetaProperty($localeCode, 'updated_at', Carbon::now()->toIso8601String());

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
    public function remove(string $localeCode, bool $save = true): self {
        return $this->set($localeCode, null, null, $save);
    }

    public function getMeta(string $localeCode): array {
        return (array) Arr::get($this->translations, "$localeCode.meta");
    }

    public function setMeta(string $localeCode, array $meta, bool $save = true): self {
        if (empty($meta)) return $this;

        $mergedMeta = array_replace($this->getMeta($localeCode), $meta);
        $translations = (array) $this->translations;
        Arr::set($translations, "$localeCode.meta", $mergedMeta);
        $this->translations = $translations;

        if ($save) $this->save();

        return $this;
    }

    public function getMetaProperty(string $localeCode, string $key): mixed {
        return Arr::get($this->getMeta($localeCode), $key);
    }

    public function setMetaProperty(string $localeCode, string $key, mixed $value, bool $save = true): self {
        return $this->setMeta($localeCode, [$key => $value], $save);
    }

    public function isMachineTranslation(string $localeCode): bool {
        return $this->has($localeCode) && $this->getMetaProperty($localeCode, 'source') === 'machine';
    }

    public function isUserTranslation(string $localeCode): bool {
        return $this->has($localeCode) && $this->getMetaProperty($localeCode, 'source') !== 'machine';
    }

    /**
     * Check if the translation for the given locale is outdated,
     * compared to the default locale.
     */
    public function isOutdated(string $localeCode) {
        $localeUpdatedAt = $this->getMetaProperty($localeCode, 'updated_at');
        if (empty($localeUpdatedAt)) return false;

        $defaultLocaleUpdatedAt = $this->getMetaProperty(Locale::getDefault()->code, 'updated_at');
        if (empty($defaultLocaleUpdatedAt)) return false;

        return Carbon::parse($localeUpdatedAt)->lessThan(Carbon::parse($defaultLocaleUpdatedAt));
    }

    /**
     * Check if there is no translation for the given locale.
     */
    public function isMissing(string $localeCode) {
        return !$this->has($localeCode);
    }

    /**
     * Get the last updated time for the given locale.
     */
    public function updatedAt(string $localeCode): CarbonInterface {
        $updatedAt = $this->getMetaProperty($localeCode, 'updated_at');
        return $updatedAt ? Carbon::parse($updatedAt) : now();
    }

    public function updateMachineTranslationsAsync(): self {
        dispatch(fn() => $this->updateMachineTranslations()->save())
            ->afterResponse();

        return $this;
    }

    /**
     * Regenerate missing or outdated machine translations, except user-edited ones.
     */
    public function updateMachineTranslations(): self {
        $targetLocales = Locale::allMachineTranslatable()
            ->filter(function ($locale) {
                // don't overwrite user translations
                if ($this->isUserTranslation($locale->code)) return false;

                // only update missing or outdated translations
                return $this->isMissing($locale->code) || $this->isOutdated($locale->code);
            });

        $translations = $this->requestMachineTranslations($targetLocales);
        foreach ($translations as $localeCode => $value) {
            // the user may have updated the translations while waiting
            // on the API, so check again to prevent overwriting user translations
            if ($this->isUserTranslation($localeCode)) continue;

            $this->set($localeCode, $value, 'machine', false);
        }

        $this->save();

        return $this;
    }

    /**
     * Request machine translations from the Google Cloud Translation API.
     */
    protected function requestMachineTranslations(Collection $targetLocales): array {
        $sourceLocale = Locale::getDefault();

        // ensure source locale is not in target locales
        $targetLocales = $targetLocales->filter(fn($l) => !$l->is($sourceLocale));
        if ($targetLocales->isEmpty()) return [];

        $sourceValue = $this->getValue($sourceLocale->code);
        if (empty($sourceValue)) return [];

        $client = new TranslationServiceClient([
            'credentials' => config('fd-cms.google_application_credentials')
        ]);
        $formattedParent = $client->locationName(config('fd-cms.google_cloud_project'), 'global');

        Log::debug("Generating translations for '{$this->key}' [Translation {$this->id}] ('" . Str::limit($sourceValue, 50) . "') in [" . $targetLocales->pluck('code')->implode(', ') . "].");

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

            if (is_string($escapedTargetValue)) {
                // replace placeholders with shortcodes again
                $targetValue = $escaper->unescape($escapedTargetValue);
                $translations[$targetLocale->code] = html_entity_decode($targetValue);
            }
        }

        return $translations;
    }

    /**
     * Get or create a translation.
     */
    public static function get(string $key, ?TranslatableInterface $record = null, ?string $group = null): Translation {
        if (empty($group) || $group === 'ungrouped') {
            $group = null;
        }

        $translation = self::getTranslations($record)
            ->where(['key' => $key, 'group' => $group])
            ->first();

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
}
