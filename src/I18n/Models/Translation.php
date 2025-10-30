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
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Feenstra\CMS\I18n\Registry;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;

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
     * Translate to the given locale (current locale by default). 
     * Will fall back to the default locale if no translation exists for the given locale.
     */
    public function translate(?string $localeCode = null): string {
        $localeCode = $localeCode ?? PageController::currentLocale()->code;
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

    public function exists() {
        return count($this->values()) > 0;
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
        $localeCode = $localeCode ?? PageController::currentLocale()->code;
        return !empty(Arr::get($this->translations, "$localeCode.value"));
    }

    /**
     * Update the translation for a given locale.
     */
    public function set(string $localeCode, mixed $value = null, string|Authenticatable|null $source = null): bool {
        if ($this->getValue($localeCode) === $value) return false;

        $isValidValue = is_string($value);

        // update the translation value
        $translations = (array) $this->translations;
        if ($isValidValue) {
            Arr::set($translations, "$localeCode.value", $value);
        } else {
            Arr::forget($translations, $localeCode);
        }
        $this->translations = $translations;

        // update meta and source (after re-assigning translations property)
        if ($isValidValue) {
            $this->setSource($localeCode, $source);
            $this->setMetaProperty($localeCode, 'updated_at', Carbon::now()->toIso8601String());
        }

        return true;
    }

    public function getUser(string $localeCode): ?Authenticatable {
        $updatedByUserId = $this->getMetaProperty($localeCode, 'updated_by_user_id');
        if (!isset($updatedByUserId)) return null;

        $model = config('fd-cms.i18n.user_model', 'App\Models\User');
        return $model::findOrFail($updatedByUserId);
    }

    public function setSource(string $localeCode, string|Authenticatable|null $source = null): self {
        if ($source instanceof Authenticatable) {
            $this->setMetaProperty($localeCode, 'updated_by_user_id', $source->getAuthIdentifier(), false);
            $this->setMetaProperty($localeCode, 'source', 'user');
        } elseif (is_string($source)) {
            $this->setMetaProperty($localeCode, 'source', $source);
        }

        return $this;
    }

    /**
     * Remove the translation for a given locale.
     */
    public function remove(string $localeCode): self {
        $this->set($localeCode, null, null);
        return $this;
    }

    public function getMeta(string $localeCode): array {
        return (array) Arr::get($this->translations, "$localeCode.meta");
    }

    public function setMeta(string $localeCode, array $meta): self {
        if (empty($meta)) return $this;

        $mergedMeta = array_replace($this->getMeta($localeCode), $meta);
        $translations = (array) $this->translations;
        Arr::set($translations, "$localeCode.meta", $mergedMeta);
        $this->translations = $translations;

        return $this;
    }

    public function getMetaProperty(string $localeCode, string $key): mixed {
        return Arr::get($this->getMeta($localeCode), $key);
    }

    public function setMetaProperty(string $localeCode, string $key, mixed $value): self {
        return $this->setMeta($localeCode, [$key => $value]);
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
        dispatch(function () {
            $this->updateMachineTranslations();
        });

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

                // check if machine translations are enabled for this key
                if ($this->record && !$this->record->shouldGenerateMachineTranslation($this->key)) {
                    return false;
                }

                // only update missing or outdated translations
                return $this->isMissing($locale->code) || $this->isOutdated($locale->code);
            });

        $translations = $this->requestMachineTranslations($targetLocales);
        foreach ($translations as $localeCode => $value) {
            // the user may have updated the translations while waiting
            // on the API, so check again to prevent overwriting user translations
            if ($this->isUserTranslation($localeCode)) continue;

            $this->set($localeCode, $value, 'machine');
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

        // if source value is empty, return null array to reset all machine translations
        $sourceValue = $this->getValue($sourceLocale->code);
        if (!Translation::isTranslateWorthy($sourceValue)) {
            return $targetLocales->keyBy('code')->map(fn() => null)->toArray();
        }

        if (!Registry::hasGoogleCloudCredentials()) {
            throw new \Exception("Cannot perform machine translations, because Google Cloud credentials are missing or incorrect.");
        }

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
                ->setParent($formattedParent)
                ->setMimeType('text/html');

            $response = $client->translateText($request);
            $escapedTargetValue = @$response->getTranslations()[0]->getTranslatedText();

            if (is_string($escapedTargetValue)) {
                // replace placeholders with shortcodes again
                $targetValue = $escaper->unescape($escapedTargetValue);
                $translations[$targetLocale->code] = html_entity_decode($targetValue);
            }
        }

        Log::debug("Successfully generated machine translations for '{$this->key}' [Translation {$this->id}]", $translations);

        return $translations;
    }

    /**
     * Get or create a translation.
     */
    public static function get(string $key, ?TranslatableInterface $record = null, ?string $group = null): Translation {
        if (empty($group) || $group === 'ungrouped') {
            $group = null;
        }

        if (isset($record)) {
            $translation = $record->translations
                ->first(fn($t) => $t->key === $key && $t->group === $group);
        } else {
            $translation = Translation::query()
                ->where(['key' => $key, 'group' => $group])
                ->first();
        }

        if (!$translation) {
            $translation = new Translation();
            $translation->key = $key;
            $translation->group = $group;
            $translation->translations = [];
            $translation->record()->associate($record);
        }

        return $translation;
    }

    /**
     * Determine if the given source value is worthy of translation. Empty strings
     * and shortcode-only strings are not.
     */
    protected static function isTranslateWorthy(?string $sourceValue): bool {
        if (!is_string($sourceValue)) return false;
        if (empty(trim($sourceValue))) return false;

        // don't translate strings that are nothing but a shortcode
        $isOnlyShortcode = preg_match('/^\s*\[.*\]\s*$/', $sourceValue);
        if ($isOnlyShortcode) return false;

        return true;
    }
}
