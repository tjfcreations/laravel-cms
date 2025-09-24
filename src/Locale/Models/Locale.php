<?php
    namespace FeenstraDigital\LaravelCMS\Locale\Models;

    use Illuminate\Database\Eloquent\Model;

    class Locale extends Model
    {
        protected $table = 'fd_cms_locales';

        protected $guarded = [];

        public function setAsDefault() {
            static::where('is_default', true)->update(['is_default' => false]);
            $this->update(['is_default' => true ]);
        }
    }