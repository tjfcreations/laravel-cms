<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use COM;
use Feenstra\CMS\I18n\Registry;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ComponentAttributeBag;
use Feenstra\CMS\Pagebuilder\Models\Menu;

class MenuItem {
    protected array $data;
    protected Collection $children;
    protected Menu $menu;

    public function __construct(array $data = [], Menu $menu) {
        $this->data = $data;
        $this->menu = $menu;

        $this->children = collect();
    }

    public function getMenu(): Menu {
        return $this->menu;
    }

    public function getType(): ?string {
        return $this->data['type'] ?? null;
    }

    public function getLabel(): ?string {
        $label = $this->data['label'] ?? '';

        $page = Page::current();
        $processor = $page->renderer->getShortcodeProcessor();
        $processedLabel = $processor->process($label, [
            'translationSources' => [
                $this->getMenu()
            ]
        ]);

        return $processedLabel;
    }

    public function getDepth(): int {
        return $this->data['depth'] ?? 0;
    }

    public function getPageId(): ?string {
        return $this->data['page_id'] ?? null;
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
        return trim($this->getUrl(), '/') === trim($currentUrl, '/') || $this->hasActiveChild();
    }

    public function hasActiveChild(): bool {
        return $this->children->contains(fn(MenuItem $child) => $child->isActive());
    }

    public function isPage(): bool {
        return $this->getType() === 'page';
    }

    public function isMailto(): bool {
        return $this->getType() === 'mailto';
    }

    public function isTel(): bool {
        return $this->getType() === 'tel';
    }

    public function isExternalUrl(): bool {
        return $this->getType() === 'url';
    }

    /**
     * Get the corresponding url based on the type of the menu item.
     */
    public function getUrl() {
        if ($this->isExternalUrl()) {
            return $this->data['external_url'] ?? null;
        }

        if ($this->isMailto()) {
            $mailto = trim($this->data['mailto'] ?? '');
            return $mailto ? 'mailto:' . $mailto : null;
        }

        if ($this->isTel()) {
            $tel = trim($this->data['tel'] ?? '');

            // remove everything that is not a number or plus sign
            $tel = preg_replace('/[^\d+]/', '', $tel);

            return $tel ? 'tel:' . $tel : null;
        }

        if ($this->isPage() && $this->getPageId()) {
            $page = Page::find($this->getPageId());
            return $page ? $page->path : null;
        }

        return null;
    }
}
