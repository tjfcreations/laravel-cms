<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use Feenstra\CMS\Pagebuilder\Models\Page;
use Feenstra\CMS\Pagebuilder\Registry;
use Feenstra\CMS\Pagebuilder\Shortcodes\ShortcodeProcessingWrapper;
use Feenstra\CMS\Pagebuilder\Shortcodes\ShortcodeProcessor;
use Illuminate\Support\Facades\View;
use Illuminate\Database\Eloquent\Model;

class PageRenderer {
    protected Page $page;
    protected array $data;
    protected ShortcodeProcessor $shortcodeProcessor;

    public function __construct(Page $page) {
        $this->page = $page;
        $this->shortcodeProcessor = new ShortcodeProcessor($this);
    }

    public function render(): string {
        $this->buildPageData();

        $content = $this->renderBlocks();

        $this->handleViewSections($content);

        return view('layouts.app', $this->data)->render();
    }

    public function getData() {
        return $this->data;
    }

    public function getShortcodeProcessor() {
        return $this->shortcodeProcessor;
    }

    protected function buildPageData() {
        $wrappedPage = $this->wrap($this->page);
        $record = $this->page->getCurrentRecord();

        $this->data = [];
        $this->data['page'] = (object) [];

        // add record
        if ($record) {
            $this->data['page']->record = $this->wrap($record);
        }

        // add page
        $this->data['page']->page = $wrappedPage;

        // add page title (after record)
        $this->data['page']->title = $wrappedPage->title;
    }

    protected function wrap(Model $record) {
        return new ShortcodeProcessingWrapper($record, $this->shortcodeProcessor);
    }

    protected function renderBlocks(): string {
        $content = '';

        if (is_array($this->page->pagebuilder)) {
            foreach ($this->page->pagebuilder as $schema) {
                if (!isset($schema['type'], $schema['data'])) {
                    continue;
                }

                $blockContent = $this->renderBlock($schema['type'], $schema['data']);
                $content .= $blockContent;
            }
        }

        return $content;
    }

    protected function renderBlock(string $type, array $props): string {
        $block = Block::findByType($type);
        if (!$block) return '';

        return $block->render($props, $this);
    }

    protected function handleViewSections(string $content): void {
        View::startSection('content');
        echo $content;
        View::stopSection();
    }
}
