<?php

namespace Feenstra\CMS\I18n\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Locale extends Model {
    protected $table = 'fd_cms_locales';

    protected $guarded = [];

    /**
     * Get all locales except the default locale (memoized).
     */
    public static function allNotDefault($columns = ['*']) {
        return once(fn() => parent::where('is_default', false)->get($columns));
    }

    /**
     * Get all machine translatable locales (memoized).
     */
    public static function allMachineTranslatable($columns = ['*']): Collection {
        return once(fn() => parent::where('is_machine_translatable', true)->get($columns));
    }

    /**
     * Get all locales with the default locale last (memoized).
     */
    public static function allWithDefaultLast($columns = ['*']) {
        return once(fn() => parent::all($columns)->sortBy('is_default'));
    }

    /**
     * Get all locales with the default locale first (memoized).
     */
    public static function allWithDefaultFirst($columns = ['*']) {
        return once(fn() => parent::all($columns)->sortByDesc('is_default'));
    }

    /**
     * Get the default locale (memoized).
     */
    public static function getDefault(): ?self {
        return once(fn() => static::where('is_default', true)->first());
    }

    /**
     * Find a locale by its code (memoized).
     */
    public static function findByCode(string $code): ?self {
        return once(fn() => static::where('code', $code)->first());
    }

    public function isDefault(): bool {
        return !!$this->is_default;
    }

    public function isMachineTranslatable(): bool {
        return !!$this->is_machine_translatable;
    }

    public function setAsDefault() {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
