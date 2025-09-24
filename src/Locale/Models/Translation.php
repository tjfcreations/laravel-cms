<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\View;
    use stdClass;
    use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Support\Str;

    class Translation extends Model
    {
        const CUSTOM_TRANSLATION_GROUP = '_custom';

        protected $table = 'fd_cms_translations';

        public $timestamps = false;

        protected $guarded = [];

        public function isCustomTranslation() {
            return str_starts_with($this->key, self::CUSTOM_TRANSLATION_GROUP.'.');
        }

        public function getUnprefixedKey() {
            if(!$this->isCustomTranslation()) return $this->key;
            return Str::substr($this->key, Str::length(Translation::CUSTOM_TRANSLATION_GROUP)+1);
        }

         /**
         * Get a translation for the current locale.
         */
        public static function get(string $key, ?TranslatableInterface $record = null): ?string {
            return static::getForLocale(app()->getLocale(), $key, $record);
        }

        /**
         * Remove a translation for the current locale.
         */
        public static function remove(string $key, ?TranslatableInterface $record = null): bool {
            static::removeForLocale(app()->getLocale(), $key, $record);
            return true;
        }

        /**
         * Set a translation for the current locale.
         */
        public static function set(string $key, string $value, ?TranslatableInterface $record = null): Translation {
            return static::setForLocale(app()->getLocale(), $key, $value, $record);
        }

        /**
         * Get a translation for a given locale.
         */
        public static function getForLocale(string $locale, string $key, ?TranslatableInterface $record = null): ?string {
            $translation = self::getTranslations($record)->where('key', $key)->where('locale', $locale)->first();
            
            if(isset($translation)) {
                return $translation->value;
            }

            return null;
        }

        /**
         * Remove a translation for a given locale.
         */
        public static function removeForLocale(string $locale, string $key, ?TranslatableInterface $record = null): bool {
            self::getTranslations($record)->where(['key' => $key, 'locale' => $locale])->delete();
            return true;
        }

        /**
         * Set a translation for a given locale.
         */
        public static function setForLocale(string $locale, string $key, string $value, ?TranslatableInterface $record = null): Translation {
            return self::getTranslations($record)->updateOrCreate(['key' => $key, 'locale' => $locale], ['value' => $value]);
        }
        
        protected static function getTranslations(?TranslatableInterface $record = null): MorphMany|Builder {
            return isset($record) ? $record->translations() : Translation::query();
        }
    }