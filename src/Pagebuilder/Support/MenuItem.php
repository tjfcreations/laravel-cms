<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use Feenstra\CMS\Pagebuilder\Models\Page;
use Illuminate\Support\Collection;

class MenuItem {
    protected array $data;
    protected Collection $children;

    public function __construct(array $data = []) {
        $this->data = $data;
        $this->children = collect();
    }

    public function getType(): ?string {
        return $this->data['type'] ?? null;
    }

    public function getLabel(): ?string {
        return $this->data['label'] ?? '';
    }

    public function getLevel(): int {
        return $this->data['level'] ?? 0;
    }

    public function getPageId(): ?string {
        return $this->data['page_id'] ?? null;
    }

    public function getUrl(): string|null {
        if ($this->getType() === 'url') {
            return $this->data['url'] ?? null;
        }

        if ($this->getType() === 'page' && $this->getPageId()) {
            $page = Page::find($this->getPageId());
            return $page ? $page->path : null;
        }

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
        $currentUrl = request()->url();
        return $this->getUrl() === $currentUrl || $this->hasActiveChild();
    }

    public function hasActiveChild(): bool {
        return $this->children->contains(fn(MenuItem $child) => $child->isActive());
    }

    public function isPage(): bool {
        return $this->getType() === 'page';
    }

    public function isExternalUrl(): bool {
        return $this->getType() === 'url';
    }

    public function toArray(): array {
        return array_merge($this->data, [
            'children' => $this->children->map(fn(MenuItem $child) => $child->toArray())->toArray()
        ]);
    }

    public static function fromArray(array $data): self {
        $item = new self($data);

        if (isset($data['children'])) {
            foreach ($data['children'] as $childData) {
                $item->addChild(self::fromArray($childData));
            }
        }

        return $item;
    }
}
