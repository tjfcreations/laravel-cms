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

    public function getTemplatableLabel(): string {
        return Str::snake(class_basename(static::class), ' ');
    }

    public function getModelPluralLabel(): string {
        return Str::plural($this->getTemplatableLabel());
    }
}
