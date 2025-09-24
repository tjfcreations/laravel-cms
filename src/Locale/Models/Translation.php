<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\View;
    use stdClass;
    use FeenstraDigital\LaravelCMS\Locale\Interfaces\TranslatableInterface;

    class Translation extends Model
    {
        protected $table = 'fd_cms_translations';

        public $timestamps = false;

        protected $guarded = [];

        public static function get(string $key, ?TranslatableInterface $record = null): ?string {
            $translations = isset($record) ? $record->translations() : Translation::query();
            $translation = $translations->where('key', $key)->where('locale', app()->getLocale())->first();
            
            if(isset($translation)) {
                return $translation->value;
            }

            return null;
        }
    }