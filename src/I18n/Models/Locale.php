<?php
    namespace Feenstra\CMS\I18n\Models;

    use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

    class Locale extends Model
    {
        protected $table = 'fd_cms_locales';

        protected $guarded = [];

        public static function allNotDefault($columns = ['*']) {
            return parent::where('is_default', false)->get($columns);
        }

        public static function allMachineTranslatable($columns = ['*']): Collection {
            return parent::where('is_machine_translatable', true)->get($columns);
        }
        
        public static function allWithDefaultLast($columns = ['*']) {
            return parent::all($columns)->sortBy('is_default');
        }

        public static function getDefault(): self {
            return static::where('is_default', true)->first();
        }

        public static function get(string $code): ?self {
            return static::where('code', $code)->first();
        }

        public function isDefault(): bool {
            return !!$this->is_default;
        }

        public function isMachineTranslatable(): bool {
            return !!$this->is_machine_translatable;
        }

        public function setAsDefault() {
            static::where('is_default', true)->update(['is_default' => false]);
            $this->update(['is_default' => true ]);
        }
    }