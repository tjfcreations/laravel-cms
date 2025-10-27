<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use Feenstra\CMS\Pagebuilder\Models\Page;

class Link {
    protected array $data;

    public function __construct(mixed $data) {
        $this->data = (array) $data;
    }

    public function type(): ?string {
        return @$this->data['type'] ?? null;
    }

    public function label(array $translationSources = []): string {
        $label = @$this->data['label'] ?? '';

        $page = page()->current();
        $processor = $page->renderer->getShortcodeProcessor();
        $processedLabel = $processor->process($label, [
            'translationSources' => $translationSources
        ]);

        return $processedLabel;
    }

    public function getPageId(): ?int {
        return (int) @$this->data['page_id'] ?? null;
    }

    public function getPageRecordId(): mixed {
        return $this->data['page_record_id'] ?? null;
    }

    public function isPage(): bool {
        return $this->type() === 'page';
    }

    public function isMailto(): bool {
        return $this->type() === 'mailto';
    }

    public function isTel(): bool {
        return $this->type() === 'tel';
    }

    public function isExternalUrl(): bool {
        return $this->type() === 'url';
    }

    /**
     * Get the corresponding url based on the type of the menu item.
     */
    public function url() {
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
            $page = $this->getPage();
            if (!$page) return null;

            if ($page->isTemplate()) {
                $recordId = $this->getPageRecordId();

                if ($recordId === 'CURRENT_PAGE_RECORD_ID') {
                    $record = page()->current()->getRequestRecord();
                } else {
                    $record = $page->getRecord(['id' => $recordId]);
                }

                if (!$record) return null;

                return $page->localizedUrl(null, $record);
            }

            return $page->localizedUrl();
        }

        return null;
    }

    public function getPage() {
        return once(function () {
            $pageId = $this->getPageId();
            return Page::findOrFail($pageId);
        });
    }
}
