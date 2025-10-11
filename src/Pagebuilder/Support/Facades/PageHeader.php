<?php

namespace Feenstra\CMS\Pagebuilder\Support\Facades;

use Feenstra\CMS\Pagebuilder\Helpers\PageHelper;
use Feenstra\CMS\Pagebuilder\Http\Controllers\PageController;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Feenstra\CMS\Pagebuilder\Support\Button;

class PageHeader {
    protected ?Collection $buttons = null;

    public function buttons(): Collection {
        if ($this->buttons === null) {
            $buttons = $this->getOption('header.buttons');
            $this->buttons = collect();

            if (is_array($buttons)) {
                foreach ($buttons as $button) {
                    $this->buttons->push(new Button($button));
                }
            }
        }

        return $this->buttons;
    }

    protected function getOption(string $path) {
        $page = PageController::currentPage();
        return Arr::get($page->options ?? [], $path);
    }
}
