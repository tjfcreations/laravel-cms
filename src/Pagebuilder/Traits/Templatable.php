<?php

namespace Feenstra\CMS\Pagebuilder\Traits;

use Feenstra\CMS\Pagebuilder\Enums\PageTypeEnum;
use Illuminate\Support\Str;
use Feenstra\CMS\Pagebuilder\Models\Page;
use Feenstra\CMS\I18n\Models\Locale;

trait Templatable {
    public function getLabel() {
        if (isset($this->label)) return $this->label;
        if (isset($this->name)) return $this->name;
        if (isset($this->title)) return $this->title;
        if (isset($this->slug)) return $this->slug;
        return $this->id;
    }

    /**
     * Get the URL of the model based on a template page.
     */
    public function getTemplatePage(): ?Page {
        return Page::where([
            'model' => get_class($this),
            'type' => PageTypeEnum::Template->value
        ])->first();
    }
}
