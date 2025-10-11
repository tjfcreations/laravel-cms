<?php

namespace Feenstra\CMS\Pagebuilder\Support\Facades;

class PageHeader {
    public ?string $title;
    public ?array $buttons;
    public ?string $description = null;
    public ?string $breadcrumb = null;

    public function title(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function description(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function buttons(array $buttons): self {
        $this->buttons = $buttons;
        return $this;
    }
}
