<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Models;

    use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

    class Locale extends Model
    {
        protected $table = 'fd_cms_locales';

        protected $guarded = [];

        public static function allNotDefault(): Collection {
            return static::whereNot('is_default', true)->get();
        }

        public function setAsDefault() {
            static::where('is_default', true)->update(['is_default' => false]);
            $this->update(['is_default' => true ]);
        }
    }