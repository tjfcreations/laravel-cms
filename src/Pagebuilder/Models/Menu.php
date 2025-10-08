<?php

namespace Feenstra\CMS\Pagebuilder\Models;

use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\I18n\Traits\Translatable;
use Feenstra\CMS\Pagebuilder\Support\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Menu extends Model implements TranslatableInterface {
    use Translatable;

    protected $table = 'fd_cms_menus';

    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translate = ['name'];

    /**
     * Get menu by location.
     */
    public static function location(string $location): Menu {
        return self::where('location', $location)->first() ?? Menu::make(['items' => []]);
    }

    public function getItems(): Collection {
        $items = collect();
        $currentParent = null;

        foreach ($this->items as $itemData) {
            $depth = $itemData['depth'] ?? 0;
            $menuItem = new MenuItem($itemData);

            if ($depth === 0) {
                // Top-level item
                $currentParent = $menuItem;
                $items->push($currentParent);
            } elseif ($depth === 1 && $currentParent !== null) {
                // Sub-item - add to current parent
                $currentParent->addChild($menuItem);
            }
        }

        return $items;
    }
}
