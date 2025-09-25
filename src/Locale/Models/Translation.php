<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Models;

    use Illuminate\Database\Eloquent\Model;
    use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

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
         * Get the translation value (current language by default).
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
         * Check if a translation exists for the given locale.
         */
        public function has(string $locale): bool {
            return !!$this->translate($locale);
        }

        /**
         * Update the translation for a given locale.
         */
        public function set(string $locale, mixed $value = null, ?string $source = null) {
            $value = is_string($value) ? $value : null;

            if(is_string($value)) {
                $translations = (array) $this->translations;
                Arr::set($translations, "$locale.value", $value);
                $this->translations = $translations;

                if(is_string($source)) {
                    $this->setMetaProperty($locale, 'source', $source, false);
                }
            }

            $this->save();

            return $this;
        }

        /**
         * Remove the translation for a given locale.
         */
        public function remove(string $locale) {
            return $this->set($locale, null);
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

            if($save) {
                $this->save();
            }

            return $this;
        }

        public function getMetaProperty(string $locale, string $key): mixed {
            return Arr::get($this->getMeta($locale), $key);
        }

        public function setMetaProperty(string $locale, string $key, mixed $value, bool $save = true): self {
            return $this->setMeta($locale, [ $key => $value ], $save);
        }

        public function isUserTranslation(string $locale): bool {
            return $this->getMetaProperty($locale, 'source') === 'user';
        }

        /**
         * Get or create a translation.
         */
        public static function get(string $key, ?TranslatableInterface $record = null, ?string $group = null): Translation {
            $translation = self::getTranslations($record)->where(['key' => $key, 'group' => $group ])->first();

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