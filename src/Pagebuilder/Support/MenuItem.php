<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use COM;
use Feenstra\CMS\I18n\Registry;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ComponentAttributeBag;
use Feenstra\CMS\Pagebuilder\Models\Menu;
use Feenstra\CMS\Pagebuilder\Support\Link;
use Feenstra\CMS\Pagebuilder\Traits\HasLink;
use Illuminate\Database\Eloquent\Model;

class MenuItem {
    use HasLink;

    protected array $data;
    protected Collection $children;
    protected Menu $menu;

    public function __construct(array $data = [], Menu $menu) {
        $this->data = $data;
        $this->menu = $menu;
        $this->link = new Link(@$data['link']);

        $this->children = collect();
    }

    public function getMenu(): Menu {
        return $this->menu;
    }

    public function getDepth(): int {
        return $this->data['depth'] ?? 0;
    }

    public function label() {
        return $this->link->label([$this->getMenu()]);
    }

    /**
     * Render the menu item.
     */
    public function render(string|array $class = [], string|array $attributes = []): string|null {
        $attributeBag = new ComponentAttributeBag($attributes);

        if (!empty($class)) {
            $attributeBag->class($class);
        }

        return Blade::render('<x-fd-cms::menu-item :item="$item" {{ $attributes }} />', [
            'item' => $this,
            'attributes' => $attributeBag
        ]);

        return null;
    }

    public function hasChildren(): bool {
        return $this->children->isNotEmpty();
    }

    public function children(): Collection {
        return $this->children;
    }

    public function addChild(MenuItem $child): self {
        $this->children->push($child);
        return $this;
    }

    public function isActive(): bool {
        $currentUrl = request()->path();
        return trim($this->url(), '/') === trim($currentUrl, '/') || $this->hasActiveChild();
    }

    public function hasActiveChild(): bool {
        return $this->children->contains(fn(MenuItem $child) => $child->isActive());
    }
}
