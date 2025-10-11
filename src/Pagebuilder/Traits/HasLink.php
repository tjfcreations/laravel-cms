<?php

namespace Feenstra\CMS\Pagebuilder\Traits;

use Feenstra\Cms\Pagebuilder\Support\Link;

trait HasLink {
    protected Link $link;

    public function getUrl() {
        return $this->link->getUrl();
    }
}
