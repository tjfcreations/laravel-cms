<?php

namespace Feenstra\CMS\Pagebuilder\Traits;

use Feenstra\CMS\Pagebuilder\Support\Link;

trait HasLink {
    protected Link $link;

    public function label() {
        return $this->link->label();
    }

    public function url() {
        return $this->link->url();
    }
}
