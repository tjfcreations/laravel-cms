<?php

namespace Feenstra\Cms\Pagebuilder\Support;

use Feenstra\CMS\Pagebuilder\Models\Page;

class Link {
    protected array $data;

    public function __construct(mixed $data) {
        $this->data = (array) $data;
    }

    public function getType(): ?string {
        return @$this->data['type'] ?? null;
    }

    public function getLabel(): string {
        return @$this->data['label'] ?? '';
    }

    public function getPageId(): ?int {
        return (int) @$this->data['page_id'] ?? null;
    }

    public function getPageRecordId(): mixed {
        return $this->data['page_record_id'] ?? null;
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
            return @$this->data['external_url'] ?? null;
        }

        if ($this->isMailto()) {
            $mailto = trim(@$this->data['mailto'] ?? '');
            return $mailto ? 'mailto:' . $mailto : null;
        }

        if ($this->isTel()) {
            $tel = trim(@$this->data['tel'] ?? '');

            // remove everything that is not a number or plus sign
            $tel = preg_replace('/[^\d+]/', '', $tel);

            return $tel ? 'tel:' . $tel : null;
        }

        if ($this->isPage()) {
            $pageId = $this->getPageId();
            $page = Page::findOrFail($pageId);
            if (!$page) return null;

            if ($page->isTemplate()) {
                $recordId = $this->getPageRecordId();
                $record = $page->getRecord(['id' => $recordId]);
                if (!$record) return null;

                return $page->localizedUrl(null, $record);
            }

            return $page->localizedUrl();
        }

        return null;
    }
}
