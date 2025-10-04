<?php
    namespace Feenstra\CMS\Locale\Models;

    use Illuminate\Database\Eloquent\Model;
    use Feenstra\CMS\Locale\Interfaces\TranslatableInterface;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Log;

    class Translation extends Model
    {
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
         * Get the translation value (current locale by default).
         */
        public function translate(?string $locale = null): ?string {
            $locale = $locale ?? app()->currentLocale();
            $value = Arr::get($this->translations, "$locale.value");
            return is_string($value) && !empty($value) ? $value : null;
        }

        /**
         * Get all translation values.
         */
        public function values(): array {
            return collect($this->translations)->map(fn($t) => @$t['value'])->toArray();
        }

        /**
         * Check if a translation exists for the given locale (current locale by default).
         */
        public function has(?string $locale = null): bool {
            $locale = $locale ?? app()->currentLocale();
            return !!$this->translate($locale);
        }

        /**
         * Update the translation for a given locale.
         */
        public function set(string $locale, mixed $value = null, ?string $source = null, bool $save = true): self {
            $value = is_string($value) ? $value : null;

            $translations = (array) $this->translations;
            Arr::set($translations, "$locale.value", $value);
            $this->translations = $translations;

            if(is_string($source)) {
                $this->setMetaProperty($locale, 'source', $source, false);
            }

            $this->setMetaProperty($locale, 'updated_at', Carbon::now()->toIso8601String());

            if($save) $this->save();

            return $this;
        }

        public function clear(bool $save = true): self {
            $this->translations = [];

            if($save) $this->save();

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
            if(empty($meta)) return $this;

            $mergedMeta = array_replace($this->getMeta($locale), $meta);
            $translations = (array) $this->translations;
            Arr::set($translations, "$locale.meta", $mergedMeta);
            $this->translations = $translations;

            if($save) $this->save();

            return $this;
        }

        public function getMetaProperty(string $locale, string $key): mixed {
            return Arr::get($this->getMeta($locale), $key);
        }

        public function setMetaProperty(string $locale, string $key, mixed $value, bool $save = true): self {
            return $this->setMeta($locale, [ $key => $value ], $save);
        }

        public function isMachineTranslation(string $locale): bool {
            return $this->has($locale) && $this->getMetaProperty($locale, 'source') === 'machine';
        }
        
        public function isUserTranslation(string $locale): bool {
            return $this->has($locale) && $this->getMetaProperty($locale, 'source') === 'user';
        }

        public function updateMachineTranslations(bool $save = true): self {
            $locales = Locale::allNotDefault();
            foreach($locales as $locale) {
                // don't override user translations
                if($this->isUserTranslation($locale->code)) continue;

                // generate machine translation and store it
                $value = $this->generateMachineTranslation($locale);
                $this->set($locale->code, $value, 'machine', false);

                Log::debug("Generated machine translation for key '{$this->key}' in locale '{$locale->code}': " . ($value ? "'$value'" : 'null'));
            }
            
            if($save) $this->save();

            return $this;
        }

        /**
         * Generates a machine translation for a given locale.
         */
        public function generateMachineTranslation(Locale $locale): bool {
            sleep(0.2);
            return fake($locale->code)->sentence();
        }

        /**
         * Get or create a translation.
         */
        public static function get(string $key, ?TranslatableInterface $record = null, string $group = ''): Translation {
            $translation = self::getTranslations($record)->where([
                'key' => $key, 
                'group' => empty($group) ? null : $group
            ])->first();

            if(!$translation) {
                $translation = new Translation();
                $translation->key = $key;
                $translation->group = $group;
                $translation->record()->associate($record);
            }

            return $translation;
        }
        
        protected static function getTranslations(?TranslatableInterface $record = null): MorphMany|Builder {
            return isset($record) ? $record->translations() : Translation::query();
        }
    }